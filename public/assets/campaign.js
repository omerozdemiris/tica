function initCampaigns() {
    function normalizeHref(href) {
        if (!href) return "#";
        const trimmed = href.trim();
        if (/^https?:\/\//i.test(trimmed)) {
            return trimmed;
        }
        if (trimmed.startsWith("/")) {
            return window.location.origin + trimmed;
        }
        return window.location.origin + "/" + trimmed;
    }

    function isExternalHref(href) {
        return /^https?:\/\//i.test((href || "").trim());
    }

    function initCampaign(selector) {
        const container = document.querySelector(selector);
        if (!container) return;

        const text = container.textContent.trim();
        const rawHref = container.dataset.href || "#";
        const href = normalizeHref(rawHref);
        const external = isExternalHref(rawHref);

        container.classList.add("campaign-ready");
        container.innerHTML = "";

        const wrapper = document.createElement("div");
        wrapper.className = "campaign-marquee";
        container.appendChild(wrapper);

        function createLink() {
            const link = document.createElement("a");
            link.href = href;
            link.className = "campaign-link text-white font-bold";
            link.textContent = text;
            if (external) {
                link.target = "_blank";
                link.rel = "noopener";
            }
            return link;
        }

        wrapper.appendChild(createLink());
        wrapper.appendChild(createLink());

        const gap = parseFloat(
            getComputedStyle(wrapper).columnGap ||
                getComputedStyle(wrapper).gap ||
                0
        );

        function fillContent() {
            const containerWidth = container.offsetWidth || window.innerWidth;
            while (wrapper.scrollWidth < containerWidth * 2) {
                wrapper.appendChild(createLink());
            }
        }

        fillContent();
        window.addEventListener("resize", fillContent);

        let offset = 0;
        let animationId;

        function step() {
            offset -= 0.7;
            wrapper.style.transform = `translateX(${offset}px)`;

            const firstChild = wrapper.children[0];
            if (!firstChild) return;
            const firstWidth = firstChild.offsetWidth + gap;

            if (Math.abs(offset) >= firstWidth) {
                offset += firstWidth;
                wrapper.appendChild(firstChild);
            }

            animationId = requestAnimationFrame(step);
        }

        animationId = requestAnimationFrame(step);

        container.addEventListener("mouseenter", function () {
            cancelAnimationFrame(animationId);
        });

        container.addEventListener("mouseleave", function () {
            animationId = requestAnimationFrame(step);
        });
    }

    initCampaign('[data-campaign="header"]');
    initCampaign('[data-campaign="footer"]');
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initCampaigns);
} else {
    initCampaigns();
}
