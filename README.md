# 💸 SMS Voucher Command Cheat Sheet

## 🛠 GENERATION COMMAND

```
GENERATE <modifiers> <dedication text>
```

You can generate one or more cash vouchers via SMS using a mix of **symbol-based modifiers** followed by a **dedication message**.

### 📌 Modifiers & Symbols

| Symbol        | Field             | Description                                                                  | Example Input         | Parsed As                     |
|---------------|------------------|------------------------------------------------------------------------------|------------------------|-------------------------------|
| `$` / `₱`     | `value`           | Voucher amount in pesos                                                      | `₱100` or `$200`       | `value = 100 / 200`           |
| `*`           | `qty`             | Number of vouchers to generate                                               | `*3`                   | `qty = 3`                     |
| `!`           | `duration`        | Voucher validity (ISO 8601 or shorthand)                                     | `!2H` or `!PT12H`       | `duration = PT2H / PT12H`     |
| `@`           | `feedback`        | Mobile number that will receive the auto-reply (e.g. a beneficiary)          | `@09171234567`         | `feedback = mobile`           |
| `#`           | `tag`             | Tag the vouchers with a campaign or category label                           | `#ReliefAid`           | `tag = ReliefAid`             |
| `&` / `>` / `:` | `mobile`        | Attach vouchers directly to a mobile number (for redemption restrictions)    | `>09171234567`         | `mobile = mobile number`      |
| *(none)*      | `dedication`      | All other words are merged as a dedication or motivational message           | `Para sa barangay`     | `dedication = "Para sa barangay"` |

## ✅ Example 1: Generate 3 vouchers at ₱200, valid for 2 hours, for mobile 09171234567

```
GENERATE ₱200 *3 !2H >09171234567 Para sa barangay hall
```

Parsed as:

```json
{
  "value": 200,
  "qty": 3,
  "duration": "PT2H",
  "mobile": "09171234567",
  "dedication": "Para sa barangay hall"
}
```

---

## 💳 REDEMPTION COMMAND

You can redeem a voucher by **sending the voucher code** via SMS.

### 🔄 Basic Format:

```
<VOUCHER_CODE>
```

✅ If valid:
- The system will **redeem the voucher** for the sender’s mobile number.
- A **witty response** or **dedication** will be returned.

❌ If invalid or already redeemed:
- **No response** is sent back.

### 👤 Specifying a Target Mobile

```
<VOUCHER_CODE> <MOBILE>
```

✅ If valid:
- The voucher is redeemed **for the specified mobile number**.
- A witty/dedication reply is returned.

Example:

```
AB12CD34EF 09175551234
```

---

## 🧪 SMS Command Summary

| Action       | Command Format                                     | Example                                  |
|--------------|----------------------------------------------------|------------------------------------------|
| Generate     | `GENERATE ₱<amount> *<qty> !<duration> @<feedback> #<tag> >/<mobile> <dedication>` | `GENERATE ₱100 *2 !PT2H >0917... Para sa inyo` |
| Redeem       | `<voucher_code>`                                   | `AB12CD34EF`                              |
| Redeem for   | `<voucher_code> <mobile>`                          | `AB12CD34EF 09175559999`                  |

---

### ✨ Notes

- **PT2H** means “valid for 2 hours” using ISO 8601 format. You may also use just `2H`.
- The first 10 voucher codes are included in SMS replies — longer batches are truncated with `…`.
- Replies are filtered to remove empty fields, and messages are crafted to be short and friendly.
