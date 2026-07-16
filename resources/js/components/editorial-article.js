const normalizeText = (value) => value.replace(/\s+/g, " ").trim();

const createHeadingId = (heading, index, usedIds) => {
    const baseId =
        heading.id ||
        heading.textContent
            .trim()
            .toLocaleLowerCase("nl")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/^-|-$/g, "") ||
        `onderdeel-${index + 1}`;
    let id = baseId;
    let duplicate = 2;

    while (
        usedIds.has(id) ||
        (document.getElementById(id) && document.getElementById(id) !== heading)
    ) {
        id = `${baseId}-${duplicate++}`;
    }

    usedIds.add(id);

    return id;
};

const initializeEditorialArticle = (root) => {
    const article = root.closest(".editorial-article");
    const prose = root.querySelector("[data-editorial-prose]");
    const navigation = root.querySelector(".editorial-toc");
    const list = root.querySelector("[data-editorial-toc]");

    if (!article || !prose || !navigation || !list) {
        return;
    }

    const lead = article.querySelector(".editorial-article__lead");
    const firstParagraph = prose.querySelector(":scope > p:first-child");

    if (
        lead &&
        firstParagraph &&
        normalizeText(lead.textContent) === normalizeText(firstParagraph.textContent)
    ) {
        firstParagraph.remove();
    }

    const usedIds = new Set();
    const sections = [...prose.querySelectorAll("h2")];
    const author = article.querySelector("[data-editorial-author]");

    sections.forEach((heading, index) => {
        heading.id = createHeadingId(heading, index, usedIds);
    });

    if (author) {
        author.id = author.id || "editorial-author";
        sections.push(author);
    }

    if (!sections.length) {
        return;
    }

    const links = sections.map((section) => {
        const item = document.createElement("li");
        const link = document.createElement("a");

        link.href = `#${section.id}`;
        link.textContent = section === author ? "Over de auteur" : section.textContent.trim();
        item.append(link);
        list.append(item);

        return link;
    });

    const setActiveSection = (section) => {
        links.forEach((link) => link.removeAttribute("aria-current"));

        const index = sections.indexOf(section);

        if (index >= 0) {
            links[index].setAttribute("aria-current", "location");
        }
    };

    const updateActiveSection = () => {
        const activationLine = 140;
        let activeSection = sections[0];

        sections.forEach((section) => {
            if (section.getBoundingClientRect().top <= activationLine) {
                activeSection = section;
            }
        });

        setActiveSection(activeSection);
    };

    const observer = new IntersectionObserver(updateActiveSection, {
        rootMargin: "-90px 0px -68% 0px",
        threshold: 0,
    });

    sections.forEach((section) => observer.observe(section));
    window.addEventListener("scroll", updateActiveSection, { passive: true });
    window.addEventListener("hashchange", updateActiveSection);

    navigation.hidden = false;
    updateActiveSection();
};

document.querySelectorAll("[data-editorial-article]").forEach(initializeEditorialArticle);
