import eslintPluginVue from "eslint-plugin-vue";
import ts from "typescript-eslint";
import eslintConfigPrettier from "eslint-config-prettier";
import eslintPluginPrettierRecommended from "eslint-plugin-prettier/recommended";

export default [
    ...ts.configs.recommended,
    ...eslintPluginVue.configs["flat/recommended"],
    eslintConfigPrettier,
    eslintPluginPrettierRecommended,
    {
        rules: {
            "vue/valid-v-slot": [
                "error",
                {
                    allowModifiers: true,
                },
            ],
            "vue/no-v-html": "off",
            "@typescript-eslint/no-explicit-any": "off",
            "prettier/prettier": [
                "error",
                {
                    endOfLine: "auto",
                    singleQuote: true,
                    semi: false,
                    printWidth: 100,
                    trailingComma: "none",
                    tabWidth: 4,
                    useTabs: false,
                },
            ],
            "vue/multi-word-component-names": "off",
        },
        files: ["resources/*.{ts,vue}", "resources/**/*.{ts,vue}"],
        languageOptions: {
            parserOptions: {
                parser: "@typescript-eslint/parser",
            },
        },
    },
];
