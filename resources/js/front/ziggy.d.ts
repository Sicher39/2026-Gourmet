type RouteFunction = (...args: unknown[]) => any;

declare module 'vue' {
    interface ComponentCustomProperties {
        route: RouteFunction;
    }
}

declare global {
    var route: RouteFunction;
}

export {};
