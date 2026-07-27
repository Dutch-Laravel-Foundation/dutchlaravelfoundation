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
