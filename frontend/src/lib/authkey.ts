/**
 * AuthKey.io OTP Service
 * ─────────────────────────────────────────────────────────────
 * Send OTP:    GET https://api.authkey.io/request
 * Verify OTP:  GET https://console.authkey.io/api/2fa_verify.php
 *
 * Configure via environment variables:
 *   NEXT_PUBLIC_AUTHKEY_KEY  — your authkey token
 *   NEXT_PUBLIC_AUTHKEY_SID  — your approved template SID
 */

export interface SendOtpResult {
  success: boolean;
  logId?: string;
  error?: string;
}

export interface VerifyOtpResult {
  success: boolean;
  error?: string;
}

const AUTHKEY    = process.env.NEXT_PUBLIC_AUTHKEY_KEY ?? "YOUR_AUTHKEY_HERE";
const AUTHKEY_SID = process.env.NEXT_PUBLIC_AUTHKEY_SID ?? "YOUR_TEMPLATE_SID";

/**
 * Send OTP to a mobile number via AuthKey.io
 * @param mobile  10-digit Indian mobile number (without +91)
 */
export async function sendOtp(mobile: string): Promise<SendOtpResult> {
  try {
    const url = new URL("https://api.authkey.io/request");
    url.searchParams.set("authkey",      AUTHKEY);
    url.searchParams.set("mobile",       mobile);
    url.searchParams.set("country_code", "91");
    url.searchParams.set("sid",          AUTHKEY_SID);

    const res = await fetch(url.toString(), { method: "GET", cache: "no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const data = await res.json();

    if (data?.LogID) {
      return { success: true, logId: data.LogID };
    }
    return { success: false, error: data?.Message ?? "Failed to send OTP. Try again." };
  } catch (err) {
    console.error("[AuthKey] sendOtp error:", err);
    return { success: false, error: "Network error. Check connection and try again." };
  }
}

/**
 * Verify OTP entered by the user against the LogID from sendOtp
 * @param otp    6-digit OTP entered by user
 * @param logId  LogID returned by sendOtp
 */
export async function verifyOtp(otp: string, logId: string): Promise<VerifyOtpResult> {
  try {
    const url = new URL("https://console.authkey.io/api/2fa_verify.php");
    url.searchParams.set("authkey", AUTHKEY);
    url.searchParams.set("channel", "sms");
    url.searchParams.set("otp",     otp);
    url.searchParams.set("logid",   logId);

    const res = await fetch(url.toString(), { method: "GET", cache: "no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const data = await res.json();

    if (data?.status === true) {
      return { success: true };
    }
    return { success: false, error: data?.message ?? "Invalid or expired OTP." };
  } catch (err) {
    console.error("[AuthKey] verifyOtp error:", err);
    return { success: false, error: "Network error during verification." };
  }
}
