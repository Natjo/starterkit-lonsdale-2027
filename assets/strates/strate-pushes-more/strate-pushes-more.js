export default function initStratePushesMore(root) {
    const list = root.querySelector(".strate-list");
    const instance = root.dataset.pushesMoreInstance;
    if (!list || !instance) return undefined;

    const controller = new AbortController();
    let request = null;

    root.addEventListener("click", async (event) => {
        const link = event.target.closest?.(".pushes-more-next[href]");
        if (!link || !root.contains(link)) return;
        event.preventDefault();
        if (request) return;

        request = new AbortController();
        link.setAttribute("aria-busy", "true");
        try {
            const response = await fetch(link.href, { signal: request.signal });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const page = new DOMParser().parseFromString(await response.text(), "text/html");
            const nextRoot = page.querySelector(`[data-pushes-more-instance="${instance}"]`);
            const nextList = nextRoot?.querySelector(".strate-list");
            if (!nextList) throw new Error("Missing pushes-more list");

            Array.from(nextList.children).forEach((item) => list.append(document.importNode(item, true)));
            const nextLink = nextRoot.querySelector(".pushes-more-next[href]");
            if (nextLink) link.href = nextLink.href;
            else link.remove();
        } catch (error) {
            if (error?.name !== "AbortError") window.location.assign(link.href);
        } finally {
            link.removeAttribute("aria-busy");
            request = null;
        }
    }, { signal: controller.signal });

    return () => {
        controller.abort();
        request?.abort();
    };
}
