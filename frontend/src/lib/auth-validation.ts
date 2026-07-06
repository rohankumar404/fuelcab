/**
 * Auth validation helpers
 */

export interface ValidationResult {
  valid: boolean;
  message: string;
}

// ─── Field validators ────────────────────────────────────────

export function validateEmail(email: string): ValidationResult {
  if (!email.trim()) return { valid: false, message: "Email is required" };
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  if (!re.test(email)) return { valid: false, message: "Enter a valid email address" };
  return { valid: true, message: "" };
}

export function validatePhone(phone: string): ValidationResult {
  const clean = phone.replace(/\D/g, "");
  if (!clean) return { valid: false, message: "Mobile number is required" };
  if (!/^[6-9]\d{9}$/.test(clean)) return { valid: false, message: "Enter a valid 10-digit Indian mobile number" };
  return { valid: true, message: "" };
}

export function validateName(name: string): ValidationResult {
  if (!name.trim()) return { valid: false, message: "Full name is required" };
  if (name.trim().length < 2) return { valid: false, message: "Name must be at least 2 characters" };
  if (!/^[a-zA-Z\s.'-]+$/.test(name)) return { valid: false, message: "Name contains invalid characters" };
  return { valid: true, message: "" };
}

export function validateCompany(company: string): ValidationResult {
  if (!company.trim()) return { valid: false, message: "Company name is required" };
  if (company.trim().length < 2) return { valid: false, message: "Enter a valid company name" };
  return { valid: true, message: "" };
}

export function validateGST(gst: string): ValidationResult {
  if (!gst.trim()) return { valid: false, message: "GST number is required" };
  const re = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
  if (!re.test(gst.toUpperCase())) return { valid: false, message: "Enter a valid 15-character GST number (e.g. 22AAAAA0000A1Z5)" };
  return { valid: true, message: "" };
}

export function validatePAN(pan: string): ValidationResult {
  if (!pan.trim()) return { valid: false, message: "PAN is required" };
  const re = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
  if (!re.test(pan.toUpperCase())) return { valid: false, message: "Enter a valid 10-character PAN (e.g. ABCDE1234F)" };
  return { valid: true, message: "" };
}

export function validatePincode(pin: string): ValidationResult {
  if (!pin) return { valid: false, message: "PIN code is required" };
  if (!/^\d{6}$/.test(pin)) return { valid: false, message: "Enter a valid 6-digit PIN code" };
  return { valid: true, message: "" };
}

// ─── Password strength ───────────────────────────────────────

export interface PasswordStrength {
  score: 0 | 1 | 2 | 3 | 4;
  label: string;
  color: string;
  suggestions: string[];
}

export function getPasswordStrength(pwd: string): PasswordStrength {
  const suggestions: string[] = [];
  let score = 0;

  if (pwd.length >= 8)  score++;  else suggestions.push("At least 8 characters");
  if (pwd.length >= 12) score++;  else if (pwd.length >= 8) suggestions.push("12+ chars for stronger security");
  if (/[A-Z]/.test(pwd)) score++; else suggestions.push("Add uppercase letters");
  if (/[0-9]/.test(pwd)) score++; else suggestions.push("Add numbers");
  if (/[^A-Za-z0-9]/.test(pwd)) score++; else suggestions.push("Add special characters (!@#$)");

  const capped = Math.min(score, 4) as 0 | 1 | 2 | 3 | 4;

  const map: Record<0 | 1 | 2 | 3 | 4, { label: string; color: string }> = {
    0: { label: "Too Weak",  color: "#ef4444" },
    1: { label: "Weak",      color: "#f97316" },
    2: { label: "Fair",      color: "#eab308" },
    3: { label: "Good",      color: "#22c55e" },
    4: { label: "Strong",    color: "#155c32" },
  };

  return { score: capped, ...map[capped], suggestions };
}

export function validatePassword(pwd: string): ValidationResult {
  if (!pwd) return { valid: false, message: "Password is required" };
  if (pwd.length < 8) return { valid: false, message: "Password must be at least 8 characters" };
  if (!/[A-Z]/.test(pwd)) return { valid: false, message: "Include at least one uppercase letter" };
  if (!/[0-9]/.test(pwd)) return { valid: false, message: "Include at least one number" };
  return { valid: true, message: "" };
}

export function validateConfirmPassword(pwd: string, confirm: string): ValidationResult {
  if (!confirm) return { valid: false, message: "Please confirm your password" };
  if (pwd !== confirm) return { valid: false, message: "Passwords do not match" };
  return { valid: true, message: "" };
}

export function validateOtp(otp: string): ValidationResult {
  if (!otp) return { valid: false, message: "Please enter the OTP" };
  if (!/^\d{6}$/.test(otp)) return { valid: false, message: "OTP must be a 6-digit number" };
  return { valid: true, message: "" };
}
