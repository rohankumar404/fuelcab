/**
 * OTP Service — proxied through the Laravel backend.
 *
 * Authkey.io is a server-to-server API.  Calling it directly from the
 * browser fails with CORS / "Invalid authkey" errors.
 *
 * Flow:
 *   Browser  →  POST /api/v1/auth/send-otp    →  Laravel  →  Authkey.io  →  SMS
 *   Browser  →  POST /api/v1/auth/verify-otp  →  Laravel  (checks cache)
 *
 * We use the phone number as the "logId" so existing call-sites that
 * pass logId into verifyOtp() continue to work without changes.
 */

const API_BASE = (process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8002")
  .replace(/\/$/, "");

export interface SendOtpResult {
  success: boolean;
  /** Stores the normalised phone so verifyOtp() knows which number to check. */
  logId?: string;
  error?: string;
}

export interface VerifyOtpResult {
  success: boolean;
  error?: string;
}

/** Normalise to +91XXXXXXXXXX */
function toE164(mobile: string): string {
  const digits = mobile.replace(/\D/g, "");
  if (digits.startsWith("91") && digits.length === 12) return `+${digits}`;
  if (digits.length === 10) return `+91${digits}`;
  return `+${digits}`;
}

/**
 * Send an OTP to the given mobile number.
 * @param mobile  10-digit number OR full +91… string
 */
export async function sendOtp(mobile: string): Promise<SendOtpResult> {
  const phone = toE164(mobile);

  try {
    const res = await fetch(`${API_BASE}/api/v1/auth/send-otp`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept":       "application/json",
      },
      body: JSON.stringify({ phone }),
      cache: "no-store",
    });

    const data = await res.json().catch(() => ({}));

    if (res.ok && data?.success) {
      return { success: true, logId: phone };
    }

    const msg =
      data?.message ??
      data?.errors?.phone?.[0] ??
      "Failed to send OTP. Try again.";

    return { success: false, error: msg };
  } catch (err) {
    console.error("[OTP] sendOtp network error:", err);
    return { success: false, error: "Network error. Check connection and try again." };
  }
}

/**
 * Verify the OTP the user entered.
 * @param otp    6-digit code entered by the user
 * @param logId  Phone number returned by sendOtp() as logId
 */
export async function verifyOtp(otp: string, logId: string): Promise<VerifyOtpResult> {
  const phone = logId; // logId == E.164 phone from sendOtp

  try {
    const res = await fetch(`${API_BASE}/api/v1/auth/verify-otp`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept":       "application/json",
      },
      body: JSON.stringify({ phone, otp }),
      cache: "no-store",
    });

    const data = await res.json().catch(() => ({}));

    if (res.ok && data?.success) {
      return { success: true };
    }

    const msg =
      data?.message ??
      data?.errors?.otp?.[0] ??
      "Invalid or expired OTP.";

    return { success: false, error: msg };
  } catch (err) {
    console.error("[OTP] verifyOtp network error:", err);
    return { success: false, error: "Network error during verification." };
  }
}
