/** @type {import('prettier').Config} */
const plugins = [
    "@ianvs/prettier-plugin-sort-imports",
    "prettier-plugin-ember-template-tag",
    "@prettier/plugin-oxc",
    "prettier-plugin-blade",
]

const base = {
    endOfLine: "lf",
    semi: false,
    singleQuote: false,
    tabWidth: 2,
    trailingComma: "es5",
    plugins,
}

module.exports = { ...base }
