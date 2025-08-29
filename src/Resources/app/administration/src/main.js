const { Application } = Shopware;

try {
  const localeFactory = Application.getContainer("factory").locale;

  if (!localeFactory.getLocaleRegistry().has("ja-JP")) {
    localeFactory.register("ja-JP", {});
  }
} catch (error) {
  console.error("Failed to register Japanese locale:", error);
}
