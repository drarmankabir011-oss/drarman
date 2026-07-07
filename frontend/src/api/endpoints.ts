import client from './client';
import { AxiosPromise } from 'axios';

// ========================================
// AUTHENTICATION
// ========================================

export const login = (email: string, password: string): AxiosPromise =>
  client.post('/auth/login', { email, password });

export const logout = (): AxiosPromise =>
  client.post('/auth/logout');

export const getCurrentUser = (): AxiosPromise =>
  client.get('/auth/me');

export const refreshToken = (): AxiosPromise =>
  client.post('/auth/refresh-token');

// ========================================
// PATIENTS
// ========================================

export const getPatients = (page = 1, limit = 50): AxiosPromise =>
  client.get('/patients', { params: { page, limit } });

export const getPatient = (id: number): AxiosPromise =>
  client.get(`/patients/${id}`);

export const createPatient = (data: any): AxiosPromise =>
  client.post('/patients', data);

export const updatePatient = (id: number, data: any): AxiosPromise =>
  client.put(`/patients/${id}`, data);

export const deletePatient = (id: number): AxiosPromise =>
  client.delete(`/patients/${id}`);

export const searchPatients = (query: string, limit = 50): AxiosPromise =>
  client.get('/patients/search', { params: { q: query, limit } });

export const getPatientTimeline = (id: number): AxiosPromise =>
  client.get(`/patients/${id}/timeline`);

// ========================================
// VITALS
// ========================================

export const recordVitals = (data: any): AxiosPromise =>
  client.post('/vitals', data);

export const getPatientVitals = (patientId: number): AxiosPromise =>
  client.get(`/patients/${patientId}/vitals`);

export const updateVitals = (id: number, data: any): AxiosPromise =>
  client.put(`/vitals/${id}`, data);

export const verifyVitals = (id: number, data: any): AxiosPromise =>
  client.put(`/vitals/${id}/verify`, data);

export const getVitalsTrends = (patientId: number): AxiosPromise =>
  client.get(`/patients/${patientId}/vitals/trends`);

// ========================================
// VISITS
// ========================================

export const createVisit = (data: any): AxiosPromise =>
  client.post('/visits', data);

export const getPatientVisits = (patientId: number): AxiosPromise =>
  client.get(`/patients/${patientId}/visits`);

export const updateVisit = (id: number, data: any): AxiosPromise =>
  client.put(`/visits/${id}`, data);

export const deleteVisit = (id: number): AxiosPromise =>
  client.delete(`/visits/${id}`);

// ========================================
// SOAP NOTES
// ========================================

export const createSoapNote = (data: any): AxiosPromise =>
  client.post('/soap-notes', data);

export const getPatientSoapNotes = (patientId: number): AxiosPromise =>
  client.get(`/patients/${patientId}/soap-notes`);

export const updateSoapNote = (id: number, data: any): AxiosPromise =>
  client.put(`/soap-notes/${id}`, data);

export const reviewSoapNote = (id: number, data: any): AxiosPromise =>
  client.put(`/soap-notes/${id}/review`, data);

export const finalizeSoapNote = (id: number, data: any): AxiosPromise =>
  client.put(`/soap-notes/${id}/finalize`, data);

export const getDailyWardRounds = (): AxiosPromise =>
  client.get('/ward-rounds/daily');

// ========================================
// PRESCRIPTIONS
// ========================================

export const createPrescription = (data: any): AxiosPromise =>
  client.post('/prescriptions', data);

export const getPatientPrescriptions = (patientId: number): AxiosPromise =>
  client.get(`/patients/${patientId}/prescriptions`);

export const updatePrescription = (id: number, data: any): AxiosPromise =>
  client.put(`/prescriptions/${id}`, data);

export const deletePrescription = (id: number): AxiosPromise =>
  client.delete(`/prescriptions/${id}`);

export const printPrescription = (id: number): AxiosPromise =>
  client.get(`/prescriptions/${id}/print`);

// ========================================
// APPOINTMENTS
// ========================================

export const getAppointments = (page = 1): AxiosPromise =>
  client.get('/appointments', { params: { page } });

export const createAppointment = (data: any): AxiosPromise =>
  client.post('/appointments', data);

export const updateAppointment = (id: number, data: any): AxiosPromise =>
  client.put(`/appointments/${id}`, data);

export const cancelAppointment = (id: number): AxiosPromise =>
  client.delete(`/appointments/${id}`);

export const getConsultantSchedule = (consultantId: number): AxiosPromise =>
  client.get(`/appointments/consultant/${consultantId}`);

// ========================================
// ALERTS
// ========================================

export const getAlerts = (): AxiosPromise =>
  client.get('/alerts');

export const getPatientAlerts = (patientId: number): AxiosPromise =>
  client.get(`/patients/${patientId}/alerts`);

export const acknowledgeAlert = (id: number): AxiosPromise =>
  client.put(`/alerts/${id}/acknowledge`);

export const dismissAlert = (id: number): AxiosPromise =>
  client.put(`/alerts/${id}/dismiss`);

// ========================================
// INVOICES & PAYMENTS
// ========================================

export const getInvoices = (page = 1): AxiosPromise =>
  client.get('/invoices', { params: { page } });

export const createInvoice = (data: any): AxiosPromise =>
  client.post('/invoices', data);

export const getInvoice = (id: number): AxiosPromise =>
  client.get(`/invoices/${id}`);

export const finalizeInvoice = (id: number): AxiosPromise =>
  client.post(`/invoices/${id}/finalize`);

export const recordPayment = (data: any): AxiosPromise =>
  client.post('/payments', data);

export const getPaymentReceipt = (id: number): AxiosPromise =>
  client.get(`/payments/${id}/receipt`);

export const sendInvoiceViaWhatsApp = (invoiceId: number): AxiosPromise =>
  client.post(`/invoices/${invoiceId}/whatsapp`);

// ========================================
// REPORTS
// ========================================

export const getDailyCensus = (): AxiosPromise =>
  client.get('/reports/daily-census');

export const getRevenueReport = (startDate: string, endDate: string): AxiosPromise =>
  client.get('/reports/revenue', { params: { startDate, endDate } });

export const getPatientStats = (): AxiosPromise =>
  client.get('/reports/patient-stats');
