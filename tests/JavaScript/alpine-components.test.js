import assert from "node:assert/strict";
import test from "node:test";

import {
    createBestPracticesFilter,
    createInternshipsFilter,
    createMembersFilter,
} from "../../resources/js/components/alpine-components.js";

const memberDocument = (titles) => ({
    querySelectorAll(selector) {
        assert.equal(selector, "#members-data [data-member]");

        return titles.map((title) => ({ dataset: { title } }));
    },
});

test("members are shuffled when the filter is initialized", () => {
    const filter = createMembersFilter({
        document: memberDocument(["Alpha", "Bravo", "Charlie"]),
        random: () => 0,
    });

    assert.deepEqual(
        filter.members.map((member) => member.title),
        ["Bravo", "Charlie", "Alpha"],
    );
});

const internshipDocument = (titles) => ({
    querySelectorAll(selector) {
        assert.equal(selector, "#internships-data [data-internship]");

        return titles.map((title) => ({ dataset: { title } }));
    },
});

test("internships are shuffled when the filter is initialized", () => {
    const filter = createInternshipsFilter({
        document: internshipDocument(["Alpha", "Bravo", "Charlie"]),
        random: () => 0,
    });

    assert.deepEqual(
        filter.internships.map((internship) => internship.title),
        ["Bravo", "Charlie", "Alpha"],
    );
});

test("best practice categories without practices are omitted", () => {
    const documentRoot = {
        querySelectorAll(selector) {
            if (selector === "#best-practices-data [data-practice]") {
                return [];
            }

            assert.equal(selector, "#best-practice-category-data [data-category]");

            return [
                { dataset: { slug: "routing", title: "Routing", count: "2" } },
                {
                    dataset: {
                        slug: "deployment",
                        title: "Deployment",
                        count: "0",
                    },
                },
            ];
        },
    };

    const filter = createBestPracticesFilter({ document: documentRoot });

    assert.deepEqual(
        filter.categories.map((category) => category.slug),
        ["routing"],
    );
});

test("best practice search can be cleared and refocused", () => {
    const documentRoot = {
        querySelectorAll() {
            return [];
        },
    };
    const filter = createBestPracticesFilter({ document: documentRoot });
    let focused = false;

    filter.query = "routing";
    filter.$refs = {
        search: {
            focus() {
                focused = true;
            },
        },
    };
    filter.$nextTick = (callback) => callback();

    assert.equal(typeof filter.clearQuery, "function");

    filter.clearQuery();

    assert.equal(filter.query, "");
    assert.equal(focused, true);
});

test("best practice filters are initialized from the URL", () => {
    const documentRoot = {
        querySelectorAll(selector) {
            if (selector === "#best-practices-data [data-practice]") {
                return [];
            }

            return [
                { dataset: { slug: "routing", title: "Routing", count: "2" } },
            ];
        },
    };
    const windowRoot = {
        location: {
            href: "https://example.test/best-practices?category=routing&search=form+request",
        },
    };

    const filter = createBestPracticesFilter({
        document: documentRoot,
        window: windowRoot,
    });

    assert.equal(filter.category, "routing");
    assert.equal(filter.query, "form request");
});

test("best practice filters keep the URL synchronized", () => {
    const documentRoot = {
        querySelectorAll(selector) {
            if (selector === "#best-practices-data [data-practice]") {
                return [];
            }

            return [
                { dataset: { slug: "routing", title: "Routing", count: "2" } },
            ];
        },
    };
    const historyCalls = [];
    const windowRoot = {
        location: { href: "https://example.test/best-practices" },
        history: {
            pushState(state, title, url) {
                historyCalls.push(["push", String(url)]);
                windowRoot.location.href = String(url);
            },
            replaceState(state, title, url) {
                historyCalls.push(["replace", String(url)]);
                windowRoot.location.href = String(url);
            },
        },
    };
    const filter = createBestPracticesFilter({
        document: documentRoot,
        window: windowRoot,
    });
    let queryWatcher;

    filter.$watch = (property, callback) => {
        assert.equal(property, "query");
        queryWatcher = callback;
    };

    assert.equal(typeof filter.init, "function");

    filter.init();
    filter.selectCategory({
        currentTarget: { dataset: { categoryValue: "routing" } },
    });
    filter.query = "form request";
    queryWatcher();

    assert.deepEqual(historyCalls, [
        ["push", "https://example.test/best-practices?category=routing"],
        [
            "replace",
            "https://example.test/best-practices?category=routing&search=form+request",
        ],
    ]);
});

test("best practice filters follow browser history navigation", () => {
    const documentRoot = {
        querySelectorAll(selector) {
            if (selector === "#best-practices-data [data-practice]") {
                return [];
            }

            return [
                { dataset: { slug: "routing", title: "Routing", count: "2" } },
            ];
        },
    };
    let popstateHandler;
    const windowRoot = {
        location: { href: "https://example.test/best-practices" },
        addEventListener(event, callback) {
            assert.equal(event, "popstate");
            popstateHandler = callback;
        },
    };
    const filter = createBestPracticesFilter({
        document: documentRoot,
        window: windowRoot,
    });

    filter.$watch = () => {};
    filter.init();

    windowRoot.location.href =
        "https://example.test/best-practices?category=routing&search=form+request";
    popstateHandler();

    assert.equal(filter.category, "routing");
    assert.equal(filter.query, "form request");
});
