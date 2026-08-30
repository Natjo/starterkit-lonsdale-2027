import initMarquee from "@components/marquee/marquee";

export default function initStrateMarquee(root) {
    const element = root.querySelector(":scope > .strate-content > .marquee");
    if (!element) return;

    const marquee = initMarquee(element);
    if (!marquee) return;

    return {
        pause: () => marquee.pause(),
        resume: () => marquee.resume(),
        destroy: () => marquee.destroy(),
    };
}
