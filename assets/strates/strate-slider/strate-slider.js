import { Slider } from "@components/slider/slider";

export default function initStrateSlider(root) {
    const element = root.querySelector(".slider");
    if (!element) return;

    const slider = new Slider(element);
    slider.add();

    return () => slider.remove();
}
