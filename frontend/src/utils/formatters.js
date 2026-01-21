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

export function formatCurrency(amount, currency = "VND") {
  const symbol = getCurrencySymbol(currency);
  const formattedAmount = Number(amount).toLocaleString();

  if (currency === "VND") {
    return `${formattedAmount} ${symbol}`;
  }
  return `${symbol}${formattedAmount}`;
}

export function formatDate(date, format = "DD/MM/YYYY") {
  if (!date) return "";
  return dayjs(date).format(format);
}

export function formatDateTime(date, format = "DD/MM/YYYY HH:mm") {
  if (!date) return "";
  return dayjs(date).format(format);
}
