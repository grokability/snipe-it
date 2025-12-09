class DIContainer {
  constructor() {
    this.services = new Map();
    this.singletons = new Map();
  }

  // Register a value or factory
  register(name, definition, options = { singleton: true }) {
    if (this.services.has(name)) {
      throw new Error(`Service '${name}' is already registered.`);
    }

    this.services.set(name, { definition, options });
  }

  // Resolve the service by name
  resolve(name) {
    const entry = this.services.get(name);

    if (!entry) {
      throw new Error(`Service '${name}' is not registered.`);
    }

    const { definition, options } = entry;

    // If it's a singleton and already created, return it
    if (options.singleton && this.singletons.has(name)) {
      return this.singletons.get(name);
    }

    // If definition is a function, call it (lazy instantiation)
    const instance = typeof definition === 'function'
      ? definition(this)
      : definition;

    if (options.singleton) {
      this.singletons.set(name, instance);
    }

    return instance;
  }
}

const container = new DIContainer();
export {
    container,
    DIContainer
};