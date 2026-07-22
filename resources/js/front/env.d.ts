/// <reference types="vite/client" />

declare module '*.vue' {
    const component: any;

    export default component;
}

declare module '*.css' {
    const content: string;

    export default content;
}
