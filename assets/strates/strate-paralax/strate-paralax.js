import { scrollAnimation, style } from "@modules/scrollDriver/scrollDriver";

export default function initStrateParalax(root) {
    const updateParalax = (progress) => {
        const offset = (progress - 0.5) * 20;
        style.var(root, `${offset}vh`, "--paralax-y");
    };

    const animation = scrollAnimation(root, {
        range: "bottom-top",
        update(event) {
            event.timeline(0, 100, updateParalax);
        },
    });

    return animation;
}
