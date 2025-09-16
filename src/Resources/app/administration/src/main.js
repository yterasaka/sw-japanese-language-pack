const { Application } = Shopware;

const localeFactory = Application.getContainer("factory").locale;

if (!localeFactory.getLocaleRegistry().has("ja-JP")) {
  try {
    localeFactory.register("ja-JP", {});
  } catch (error) {
    console.error("Failed to register Japanese locale:", error);
  }
}
