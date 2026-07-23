import hljs from "highlight.js/lib/core";
import bash from "highlight.js/lib/languages/bash";
import css from "highlight.js/lib/languages/css";
import graphql from "highlight.js/lib/languages/graphql";
import javascript from "highlight.js/lib/languages/javascript";
import json from "highlight.js/lib/languages/json";
import php from "highlight.js/lib/languages/php";
import shell from "highlight.js/lib/languages/shell";
import sql from "highlight.js/lib/languages/sql";
import typescript from "highlight.js/lib/languages/typescript";
import xml from "highlight.js/lib/languages/xml";
import yaml from "highlight.js/lib/languages/yaml";

import "highlight.js/styles/github.css";

const languages = {
    bash,
    css,
    graphql,
    javascript,
    json,
    php,
    shell,
    sql,
    typescript,
    xml,
    yaml,
};

Object.entries(languages).forEach(([name, language]) => {
    hljs.registerLanguage(name, language);
});

document.querySelectorAll("pre code").forEach((element) => {
    hljs.highlightElement(element);
});
