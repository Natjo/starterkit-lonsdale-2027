const focusableSelector = 'a[href], button, input, select, textarea, [tabindex]';

class Marquee {
    constructor(root) {
        if (!(root instanceof HTMLElement)) return;
        if (root.__marqueeInstance) return root.__marqueeInstance;

        this.root = root;
        this.track = root.querySelector(':scope > [data-marquee-track]');
        this.group = this.track?.querySelector(':scope > [data-marquee-group]');
        if (!this.track || !this.group) return;

        this.speed = Math.min(400, Math.max(10, Number(root.dataset.marqueeSpeed) || 60));
        this.direction = root.dataset.marqueeDirection === 'right' ? 'right' : 'left';
        this.baseDirection = this.direction;
        this.scrollAcceleration = root.dataset.marqueeScrollAcceleration !== 'false';
        this.followScrollDirection = this.scrollAcceleration
            && root.dataset.marqueeFollowScrollDirection !== 'false';
        this.frame = 0;
        this.momentumFrame = 0;
        this.clickTimer = 0;
        this.destroyed = false;
        this.lifecyclePaused = false;
        this.sizeObserversActive = false;
        this.playbackRate = 1;
        this.scrollSign = 0;
        this.animation = null;
        this.measurement = null;
        this.lenis = null;
        this.drag = null;
        this.suppressClick = false;
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.refresh = this.refresh.bind(this);
        this.scheduleRefresh = this.scheduleRefresh.bind(this);
        this.onPointerDown = this.onPointerDown.bind(this);
        this.onPointerMove = this.onPointerMove.bind(this);
        this.onPointerEnd = this.onPointerEnd.bind(this);
        this.onPointerCancel = this.onPointerCancel.bind(this);
        this.onDragStart = this.onDragStart.bind(this);
        this.onClick = this.onClick.bind(this);
        this.onPointerLeave = this.onPointerLeave.bind(this);
        this.onLenisScroll = this.onLenisScroll.bind(this);
        this.onLenisReady = this.onLenisReady.bind(this);

        this.resizeObserver = 'ResizeObserver' in window
            ? new ResizeObserver(this.scheduleRefresh)
            : null;
        this.connectSizeObservers();
        this.reducedMotion.addEventListener?.('change', this.scheduleRefresh);
        root.addEventListener('pointerdown', this.onPointerDown);
        root.addEventListener('pointermove', this.onPointerMove);
        root.addEventListener('pointerup', this.onPointerEnd);
        root.addEventListener('pointercancel', this.onPointerCancel);
        root.addEventListener('pointerleave', this.onPointerLeave);
        root.addEventListener('dragstart', this.onDragStart);
        root.addEventListener('click', this.onClick, true);
        if (this.scrollAcceleration) window.addEventListener('lenis:ready', this.onLenisReady);

        document.fonts?.ready.then(() => {
            if (!this.destroyed) this.scheduleRefresh();
        });

        root.__marqueeInstance = this;
        if (this.scrollAcceleration) this.connectLenis();
        this.scheduleRefresh();
    }

    scheduleRefresh() {
        if (this.destroyed || this.lifecyclePaused) return;
        cancelAnimationFrame(this.frame);
        this.frame = requestAnimationFrame(this.refresh);
    }

    connectSizeObservers() {
        if (this.destroyed || this.sizeObserversActive) return;

        this.sizeObserversActive = true;
        this.resizeObserver?.observe(this.root);
        this.resizeObserver?.observe(this.group);
        window.addEventListener('resize', this.scheduleRefresh, { passive: true });
    }

    disconnectSizeObservers() {
        if (!this.sizeObserversActive) return;

        this.sizeObserversActive = false;
        this.resizeObserver?.disconnect();
        window.removeEventListener('resize', this.scheduleRefresh);
    }

    removeClones() {
        this.track.querySelectorAll(':scope > [data-marquee-clone]').forEach((clone) => clone.remove());
    }

    createClone() {
        const clone = this.group.cloneNode(true);
        clone.setAttribute('data-marquee-clone', '');
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('[id]').forEach((element) => element.removeAttribute('id'));
        clone.querySelectorAll(focusableSelector).forEach((element) => element.setAttribute('tabindex', '-1'));
        this.track.append(clone);
    }

    refresh() {
        this.frame = 0;
        if (this.destroyed) return;

        if (this.reducedMotion.matches) {
            this.cancelMomentum();
            this.cancelDrag();
            this.root.classList.remove('is-ready');
            this.animation = null;
            this.measurement = null;
            this.removeClones();
            return;
        }

        const distance = this.group.getBoundingClientRect().width;
        const viewport = this.root.getBoundingClientRect().width;
        const cloneCount = distance > 0 && viewport > 0
            ? Math.max(1, Math.ceil(viewport / distance))
            : 0;
        const existingCloneCount = this.track.querySelectorAll(':scope > [data-marquee-clone]').length;
        const unchanged = this.root.classList.contains('is-ready')
            && this.measurement
            && Math.abs(this.measurement.distance - distance) < .5
            && existingCloneCount === cloneCount;

        if (unchanged) return;

        this.cancelMomentum();
        this.cancelDrag();
        this.root.classList.remove('is-ready');
        this.animation = null;
        this.measurement = null;
        this.removeClones();

        if (cloneCount === 0) return;

        for (let index = 0; index < cloneCount; index += 1) this.createClone();

        this.measurement = { distance };
        this.root.style.setProperty('--marquee-distance', `${distance}px`);
        this.root.style.setProperty('--marquee-duration', `${distance / this.speed}s`);
        this.root.classList.add('is-ready');
        this.setPlaybackRate(this.playbackRate, true);
    }

    getAnimation() {
        if (this.animation) return this.animation;

        this.animation = this.track.getAnimations()
            .find((animation) => animation.animationName === 'marquee-left') || null;

        return this.animation;
    }

    onPointerDown(event) {
        if (!event.isPrimary || event.button !== 0 || this.reducedMotion.matches) return;

        const animation = this.getAnimation();
        if (!animation) return;

        this.cancelMomentum();
        this.drag = {
            id: event.pointerId,
            x: event.clientX,
            time: Number(animation.currentTime) || 0,
            animation,
            moved: false,
            lastX: event.clientX,
            lastTime: event.timeStamp,
            velocity: 0,
        };
        this.root.classList.add('is-dragging');
        animation.pause();
        try { this.root.setPointerCapture(event.pointerId); } catch (_) { /* synthetic event */ }
    }

    onPointerMove(event) {
        if (!this.drag || event.pointerId !== this.drag.id) return;

        const delta = event.clientX - this.drag.x;
        const elapsed = event.timeStamp - this.drag.lastTime;
        if (elapsed > 0) {
            const velocity = (event.clientX - this.drag.lastX) / elapsed;
            this.drag.velocity = (this.drag.velocity * .5) + (velocity * .5);
            this.drag.lastX = event.clientX;
            this.drag.lastTime = event.timeStamp;
        }
        if (Math.abs(delta) > 4) this.drag.moved = true;
        if (!this.drag.moved) return;

        const duration = Number(this.drag.animation.effect?.getTiming().duration) || 1;
        const direction = this.direction === 'right' ? 1 : -1;
        const time = this.drag.time + (delta * direction / this.speed * 1000);
        this.drag.animation.currentTime = ((time % duration) + duration) % duration;
        event.preventDefault();
    }

    onPointerEnd(event) {
        if (!this.drag || event.pointerId !== this.drag.id) return;

        const { animation, moved, lastTime } = this.drag;
        let { velocity } = this.drag;
        this.drag = null;
        this.root.classList.remove('is-dragging');

        if (moved) {
            this.root.classList.add('is-dragged');
            this.suppressClick = true;
            window.clearTimeout(this.clickTimer);
            this.clickTimer = window.setTimeout(() => { this.suppressClick = false; }, 0);

            const idle = Math.max(0, event.timeStamp - lastTime);
            velocity *= Math.max(0, 1 - (idle / 160));
        }

        const activeAnimation = moved && Math.abs(velocity) > .03
            ? this.setDirection(velocity > 0 ? 'right' : 'left', animation)
            : animation;

        if (!this.lifecyclePaused) {
            if (moved && Math.abs(velocity) > .03) this.startMomentum(activeAnimation, velocity);
            else activeAnimation.play();
        }
        try { this.root.releasePointerCapture(event.pointerId); } catch (_) { /* synthetic event */ }
    }

    onPointerCancel(event) {
        if (!this.drag || event.pointerId !== this.drag.id) return;
        this.cancelDrag();
    }

    cancelDrag() {
        if (!this.drag) return;

        const { animation, id, moved } = this.drag;
        this.drag = null;
        this.root.classList.remove('is-dragging');

        if (moved && !this.destroyed) {
            this.suppressClick = true;
            window.clearTimeout(this.clickTimer);
            this.clickTimer = window.setTimeout(() => { this.suppressClick = false; }, 500);
        }

        if (!this.lifecyclePaused && !this.destroyed) animation.play();
        try { this.root.releasePointerCapture(id); } catch (_) { /* synthetic event */ }
    }

    setDirection(direction, animation = this.getAnimation(), persist = true) {
        const nextDirection = direction === 'right' ? 'right' : 'left';
        if (persist) {
            this.baseDirection = nextDirection;
            this.scrollSign = 0;
        }
        if (!animation || nextDirection === this.direction) return animation;

        this.cancelMomentum();
        const duration = Number(animation.effect?.getTiming().duration) || 1;
        const currentTime = Math.max(0, Number(animation.currentTime) || 0);
        const iteration = Math.floor(currentTime / duration);
        const progressTime = currentTime % duration;

        this.direction = nextDirection;
        this.root.dataset.marqueeDirection = nextDirection;
        animation.currentTime = (iteration * duration) + ((duration - progressTime) % duration);
        if (this.lifecyclePaused || this.drag) animation.pause();
        else animation.play();

        return animation;
    }

    startMomentum(animation, velocity) {
        this.cancelMomentum();
        // La timeline CSS tourne déjà à sa vitesse normale pendant que le RAF
        // ajoute l'inertie : à la fin, il suffit d'arrêter de la corriger.
        animation.play();

        const baseVelocity = (this.direction === 'right' ? 1 : -1) * this.speed * this.playbackRate / 1000;
        const velocityDelta = velocity - baseVelocity;
        const duration = Math.min(500, Math.max(180, Math.abs(velocityDelta) * 320));
        const animationDuration = Number(animation.effect?.getTiming().duration) || 1;
        const direction = this.direction === 'right' ? 1 : -1;
        const from = Number(animation.currentTime) || 0;
        const startedAt = performance.now();

        const tick = (now) => {
            const elapsed = Math.min(duration, now - startedAt);
            const progress = elapsed / duration;
            // Intègre une vitesse qui rejoint progressivement la vitesse normale :
            // aucune phase à 0 entre l'inertie et la reprise de l'animation CSS.
            const blend = progress - Math.pow(progress, 2) + (Math.pow(progress, 3) / 3);
            const distance = (baseVelocity * elapsed) + (velocityDelta * duration * blend);
            const time = from + (distance * direction / this.speed * 1000);
            animation.currentTime = ((time % animationDuration) + animationDuration) % animationDuration;

            if (progress < 1 && !this.lifecyclePaused && !this.destroyed) {
                this.momentumFrame = requestAnimationFrame(tick);
                return;
            }

            this.momentumFrame = 0;
        };

        this.momentumFrame = requestAnimationFrame(tick);
    }

    cancelMomentum() {
        cancelAnimationFrame(this.momentumFrame);
        this.momentumFrame = 0;
    }

    setPlaybackRate(rate, force = false) {
        const nextRate = Math.min(6, Math.max(1, rate));
        if (!force && Math.abs(nextRate - this.playbackRate) < .03) return;

        this.playbackRate = nextRate;
        const animation = this.getAnimation();
        if (animation?.updatePlaybackRate) animation.updatePlaybackRate(this.playbackRate);
        else if (animation) animation.playbackRate = this.playbackRate;
    }

    onLenisScroll(lenis) {
        const signedVelocity = Number(lenis?.velocity) || 0;
        const velocity = Math.abs(signedVelocity);
        this.root.classList.toggle('is-scroll-accelerated', velocity > .01);

        if (this.followScrollDirection && velocity > .01) {
            const scrollSign = Math.sign(Number(lenis?.direction) || signedVelocity);

            if (scrollSign !== 0 && scrollSign !== this.scrollSign) {
                this.scrollSign = scrollSign;
                const oppositeDirection = this.baseDirection === 'left' ? 'right' : 'left';
                const nextDirection = scrollSign < 0 ? oppositeDirection : this.baseDirection;
                this.setDirection(nextDirection, this.getAnimation(), false);
            }
        }

        this.setPlaybackRate(1 + (Math.sqrt(velocity) * 1.3), velocity <= .01);
    }

    onLenisReady(event) {
        this.connectLenis(event.detail);
    }

    connectLenis(instance = null) {
        if (!this.scrollAcceleration) return;

        const connect = (lenis) => {
            if (this.destroyed || this.lifecyclePaused || this.lenis || !lenis?.on) return;
            this.lenis = lenis;
            lenis.on('scroll', this.onLenisScroll);
        };

        if (instance?.on) connect(instance);
        else if (window.lenis?.on) connect(window.lenis);
    }

    disconnectLenis() {
        this.lenis?.off?.('scroll', this.onLenisScroll);
        this.lenis = null;
        this.scrollSign = 0;
        this.root.classList.remove('is-scroll-accelerated');
        this.setPlaybackRate(1, true);
    }

    onPointerLeave() {
        if (!this.drag) this.root.classList.remove('is-dragged');
    }

    onDragStart(event) {
        event.preventDefault();
    }

    onClick(event) {
        if (!this.suppressClick) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        this.suppressClick = false;
    }

    pause() {
        this.lifecyclePaused = true;
        cancelAnimationFrame(this.frame);
        this.frame = 0;
        this.cancelMomentum();
        this.disconnectSizeObservers();
        this.disconnectLenis();
        this.root.classList.add('is-paused');
        this.getAnimation()?.pause();
    }

    resume() {
        this.lifecyclePaused = false;
        this.connectSizeObservers();
        this.root.classList.remove('is-paused');
        this.connectLenis();
        if (!this.drag) this.getAnimation()?.play();
        this.scheduleRefresh();
    }

    destroy() {
        this.destroyed = true;
        cancelAnimationFrame(this.frame);
        this.frame = 0;
        this.cancelMomentum();
        this.cancelDrag();
        this.disconnectLenis();
        this.disconnectSizeObservers();
        window.clearTimeout(this.clickTimer);
        this.reducedMotion.removeEventListener?.('change', this.scheduleRefresh);
        window.removeEventListener('lenis:ready', this.onLenisReady);
        this.root.removeEventListener('pointerdown', this.onPointerDown);
        this.root.removeEventListener('pointermove', this.onPointerMove);
        this.root.removeEventListener('pointerup', this.onPointerEnd);
        this.root.removeEventListener('pointercancel', this.onPointerCancel);
        this.root.removeEventListener('pointerleave', this.onPointerLeave);
        this.root.removeEventListener('dragstart', this.onDragStart);
        this.root.removeEventListener('click', this.onClick, true);
        this.removeClones();
        this.animation = null;
        this.measurement = null;
        this.root.classList.remove('is-ready', 'is-paused', 'is-dragging', 'is-dragged', 'is-scroll-accelerated');
        this.root.style.removeProperty('--marquee-distance');
        this.root.style.removeProperty('--marquee-duration');
        delete this.root.__marqueeInstance;
    }
}

export { Marquee };

export default function initMarquee(root) {
    const instance = new Marquee(root);
    if (!instance?.root) return;

    return instance;
}
