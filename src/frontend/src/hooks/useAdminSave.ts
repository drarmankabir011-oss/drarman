import type {
  DoctorAccount,
  PatientAccount,
} from "./useEmailAuth";
import {
  loadPatientRegistry,
  loadRegistry,
  savePatientRegistry,
  saveRegistry,
  appendAuditLog,
  STAFF_ROLE_LABELS,
} from "./useEmailAuth";
import type { StaffRole } from "../types";

/**
 * Enhanced save with error handling, quota detection, and debugging
 */
export const enhancedSaveRegistry = (registry: DoctorAccount[]) => {
  try {
    const REGISTRY_KEY = "medicare_registry";
    const serialized = JSON.stringify(registry);
    
    // Check size before saving
    if (serialized.length > 4 * 1024 * 1024) {
      console.error(
        `[Storage] Registry too large: ${serialized.length} bytes`,
      );
      throw new Error("Registry data exceeds storage limit");
    }

    localStorage.setItem(REGISTRY_KEY, serialized);

    // Verify the save worked
    const verify = localStorage.getItem(REGISTRY_KEY);
    if (!verify) {
      throw new Error("Failed to verify registry save");
    }

    console.log(`[Storage] Registry saved: ${registry.length} accounts`);
    return true;
  } catch (err: unknown) {
    const errMsg =
      err instanceof Error ? err.message : String(err);
    console.error(`[Storage Error] Failed to save registry:`, errMsg);
    
    // Log quota errors
    if (errMsg.includes("QuotaExceeded") || errMsg.includes("quota")) {
      console.error(
        "[Storage] LocalStorage quota exceeded. Try clearing old data.",
      );
    }
    
    return false;
  }
};

export const enhancedSavePatientRegistry = (registry: PatientAccount[]) => {
  try {
    const PATIENT_REGISTRY_KEY = "medicare_patient_registry";
    const serialized = JSON.stringify(registry);
    
    // Check size before saving
    if (serialized.length > 4 * 1024 * 1024) {
      console.error(
        `[Storage] Patient registry too large: ${serialized.length} bytes`,
      );
      throw new Error("Patient registry data exceeds storage limit");
    }

    localStorage.setItem(PATIENT_REGISTRY_KEY, serialized);

    // Verify the save worked
    const verify = localStorage.getItem(PATIENT_REGISTRY_KEY);
    if (!verify) {
      throw new Error("Failed to verify patient registry save");
    }

    console.log(
      `[Storage] Patient registry saved: ${registry.length} patients`,
    );
    return true;
  } catch (err: unknown) {
    const errMsg =
      err instanceof Error ? err.message : String(err);
    console.error(
      `[Storage Error] Failed to save patient registry:`,
      errMsg,
    );
    return false;
  }
};

/**
 * Admin action: Approve staff account
 */
export const approveStaffAccount = (
  accountId: string,
  selectedRole: StaffRole,
): boolean => {
  try {
    const reg = loadRegistry();
    const idx = reg.findIndex((d) => d.id === accountId);
    
    if (idx < 0) {
      console.error(`[Admin] Account not found: ${accountId}`);
      return false;
    }

    const oldRole = reg[idx].role;
    reg[idx] = { ...reg[idx], status: "approved", role: selectedRole };

    const success = enhancedSaveRegistry(reg);
    if (success) {
      appendAuditLog({
        timestamp: new Date().toISOString(),
        userRole: "admin",
        userName: "Admin",
        action: `Approved account as ${STAFF_ROLE_LABELS[selectedRole] ?? selectedRole}`,
        target: reg[idx].name,
      });
    }

    return success;
  } catch (err: unknown) {
    console.error("[Admin] Failed to approve staff:", err);
    return false;
  }
};

/**
 * Admin action: Reject staff account
 */
export const rejectStaffAccount = (accountId: string): boolean => {
  try {
    const reg = loadRegistry();
    const idx = reg.findIndex((d) => d.id === accountId);
    
    if (idx < 0) {
      console.error(`[Admin] Account not found: ${accountId}`);
      return false;
    }

    const staffName = reg[idx].name;
    reg[idx] = { ...reg[idx], status: "rejected" };

    const success = enhancedSaveRegistry(reg);
    if (success) {
      appendAuditLog({
        timestamp: new Date().toISOString(),
        userRole: "admin",
        userName: "Admin",
        action: "Rejected account",
        target: staffName,
      });
    }

    return success;
  } catch (err: unknown) {
    console.error("[Admin] Failed to reject staff:", err);
    return false;
  }
};

/**
 * Admin action: Approve patient account
 */
export const approvePatientAccount = (patientId: string): boolean => {
  try {
    const reg = loadPatientRegistry();
    const idx = reg.findIndex((p) => p.id === patientId);
    
    if (idx < 0) {
      console.error(`[Admin] Patient not found: ${patientId}`);
      return false;
    }

    const patientName = reg[idx].name;
    reg[idx] = { ...reg[idx], status: "approved" };

    const success = enhancedSavePatientRegistry(reg);
    if (success) {
      appendAuditLog({
        timestamp: new Date().toISOString(),
        userRole: "admin",
        userName: "Admin",
        action: "Approved patient account",
        target: patientName,
      });
    }

    return success;
  } catch (err: unknown) {
    console.error("[Admin] Failed to approve patient:", err);
    return false;
  }
};

/**
 * Admin action: Reject patient account
 */
export const rejectPatientAccount = (patientId: string): boolean => {
  try {
    const reg = loadPatientRegistry();
    const idx = reg.findIndex((p) => p.id === patientId);
    
    if (idx < 0) {
      console.error(`[Admin] Patient not found: ${patientId}`);
      return false;
    }

    const patientName = reg[idx].name;
    reg[idx] = { ...reg[idx], status: "rejected" };

    const success = enhancedSavePatientRegistry(reg);
    if (success) {
      appendAuditLog({
        timestamp: new Date().toISOString(),
        userRole: "admin",
        userName: "Admin",
        action: "Rejected patient account",
        target: patientName,
      });
    }

    return success;
  } catch (err: unknown) {
    console.error("[Admin] Failed to reject patient:", err);
    return false;
  }
};

/**
 * Admin action: Reassign staff role
 */
export const reassignStaffRole = (
  accountId: string,
  newRole: StaffRole,
): boolean => {
  try {
    const reg = loadRegistry();
    const idx = reg.findIndex((d) => d.id === accountId);
    
    if (idx < 0) {
      console.error(`[Admin] Account not found: ${accountId}`);
      return false;
    }

    const oldRole = reg[idx].role as StaffRole;
    const staffName = reg[idx].name;

    // Don't update if role is the same
    if (oldRole === newRole) {
      console.warn(`[Admin] Role unchanged for ${staffName}: ${oldRole}`);
      return false;
    }

    reg[idx] = { ...reg[idx], role: newRole };

    const success = enhancedSaveRegistry(reg);
    if (success) {
      appendAuditLog({
        timestamp: new Date().toISOString(),
        userRole: "admin",
        userName: "Admin",
        action: `Role changed: ${STAFF_ROLE_LABELS[oldRole] ?? oldRole} → ${STAFF_ROLE_LABELS[newRole]}`,
        target: staffName,
      });
    }

    return success;
  } catch (err: unknown) {
    console.error("[Admin] Failed to reassign role:", err);
    return false;
  }
};

/**
 * Verify localStorage is working and report usage
 */
export const verifyStorageCapacity = (): {
  available: boolean;
  percentUsed: number;
  message: string;
} => {
  try {
    const test = "__storage_test__";
    localStorage.setItem(test, "test");
    const item = localStorage.getItem(test);
    localStorage.removeItem(test);

    if (item !== "test") {
      return {
        available: false,
        percentUsed: 100,
        message: "localStorage verification failed",
      };
    }

    let totalSize = 0;
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key) {
        totalSize +=
          key.length + (localStorage.getItem(key)?.length || 0);
      }
    }

    const approxLimit = 5 * 1024 * 1024; // 5MB typical
    const percentUsed = Math.round((totalSize / approxLimit) * 100);

    return {
      available: true,
      percentUsed,
      message: `Storage: ${(totalSize / 1024).toFixed(2)}KB / ~${(approxLimit / 1024 / 1024).toFixed(0)}MB`,
    };
  } catch (err: unknown) {
    const errMsg =
      err instanceof Error ? err.message : String(err);
    return {
      available: false,
      percentUsed: 100,
      message: `Storage error: ${errMsg}`,
    };
  }
};
