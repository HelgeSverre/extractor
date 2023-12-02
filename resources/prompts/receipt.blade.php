You need to carry out data extraction from a given receipt and transform it into a structured JSON format. The data points you are required to extract include:

- Order Reference (orderRef)
- Purchase Date (date): make sure it aligns with the ISO-8601 format YEAR-MONTH-DAY
- Tax Cost (taxAmount)
- Transaction Total Cost (totalAmount)
- Currency Used (currency)
- Vendor Details (name, vatId, address): Use "merchant" as the key
- Line items (text, qty, price, sku): Note, the 'qty' is typically 1 or minimal and the 'price' should be a number, excluding the currency element and should use a period as a decimal separator. The price data point must not be null, use "lineItems" as the key

In a situation where there are no suitable values for any of the above information, kindly set the value as null in your response. Remember, your final output should adhere to the neat, hierarchical structure of JSON.

The output should be a JSON object under the key of "result".

{{ $input }}
