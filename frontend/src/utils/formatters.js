/**
 * Formatters Utility
 *
 * Helper functions for formatting currency, dates, and metal units.
 */
import dayjs from "dayjs";

export const CURRENCY_SYMBOLS = {
  VND: "₫",
  USD: "$",
  EUR: "€",
  JPY: "¥",
  GBP: "£",
};

export function getCurrencySymbol(currency) {
  return CURRENCY_SYMBOLS[currency] || currency;
}

export function formatCurrency(amount, currency = "VND", locale = "vi-VN") {
  if (amount === null || amount === undefined) return "N/A";

  return new Intl.NumberFormat(locale, {
    style: "currency",
    currency: currency,
  }).format(amount);
}

// Gold/Silver Unit Conversion Constants
export const UNIT_CONVERSION = {
  OZ_TO_LUONG: 0.829426, // 1 oz = 0.829426 lượng
  OZ_TO_CHI: 8.29426, // 1 oz = 8.29426 chỉ (10 chỉ = 1 lượng)
};

/**
 * Convert metal price from USD/oz to target unit and currency
 * @param {number} priceUsd - Price in USD per ounce
 * @param {string} targetUnit - Target unit (oz, lượng, chỉ)
 * @param {string} targetCurrency - Target currency (VND, USD, etc.)
 * @param {object} exchangeRates - Object containing rates (e.g., { usd_vnd: 25000 })
 * @param {string} locale - Locale for formatting
 * @returns {string} Formatted price string
 */
export function formatMetalPrice(
  priceValue,
  targetUnit,
  targetCurrency,
  exchangeRates,
  locale = "vi-VN",
  sourceUnit = "oz",
  sourceCurrency = "USD",
) {
  if (!priceValue) return "N/A";

  let price = priceValue;

  // 1. Convert Currency
  if (sourceCurrency !== targetCurrency) {
    if (
      sourceCurrency === "USD" &&
      targetCurrency === "VND" &&
      exchangeRates?.usd_vnd
    ) {
      price = price * exchangeRates.usd_vnd;
    }
    // Add inverse or other pairs if needed
  }

  // 2. Convert Unit
  if (sourceUnit !== targetUnit) {
    // Convert source to oz first (normalization)
    let pricePerOz = price;
    if (sourceUnit === "lượng") {
      // 1 lượng = 1.20565 oz
      // Price/lượng -> Price/oz = Price/lượng / 1.20565
      pricePerOz = price / 1.20565;
    } else if (sourceUnit === "chỉ") {
      // 1 chỉ = 0.120565 oz
      pricePerOz = price / 0.120565;
    }

    // Convert oz to target unit
    if (targetUnit === "lượng") {
      // Price/oz -> Price/lượng = Price/oz * 1.20565
      price = pricePerOz * 1.20565;
    } else if (targetUnit === "chỉ") {
      // Price/oz -> Price/chỉ = Price/oz * 0.120565
      price = pricePerOz * 0.120565;
    } else if (targetUnit === "oz") {
      price = pricePerOz;
    }
  }

  return formatCurrency(price, targetCurrency, locale);
}

export function formatDate(date, format = "DD/MM/YYYY") {
  if (!date) return "";
  return dayjs(date).format(format);
}

export function formatDateTime(date, format = "DD/MM/YYYY HH:mm") {
  if (!date) return "";
  return dayjs(date).format(format);
}
