import fetchWithAuth, { API_CONFIG, type PaginatedResponse, type ApiResponse } from './api';

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  icon?: string;
  color?: string;
  spectacles_count?: number;
}

export interface Hall {
  id: number;
  name: string;
  location?: string;
  capacity: number;
  description?: string;
  type?: string;
  is_active: boolean;
  image_url?: string;
  amenities?: string[];
}

export interface Spectacle {
  id: number;
  title: string;
  description?: string;
  duration?: number;
  base_price: number;
  image_url?: string;
  poster_url?: string;
  trailer_url?: string;
  language?: string;
  age_restriction?: number;
  category_id?: number;
  category?: Category;
  director?: string;
  actors?: string[];
  is_published: boolean;
  status: 'upcoming' | 'ongoing' | 'finished' | 'cancelled';
  created_at?: string;
}

export interface Seance {
  id: number;
  spectacle_id: number;
  hall_id: number;
  date_seance: string;
  available_seats: number;
  price: number;
  status: 'scheduled' | 'cancelled' | 'completed';
  spectacle?: Spectacle;
  hall?: Hall;
  bookings_count?: number;
  remaining_seats?: number;
}

export interface Reservation {
  id: number;
  user_id: number;
  seance_id: number;
  booking_reference: string;
  quantity: number;
  total_price: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'expired';
  payment_status: 'pending' | 'paid' | 'refunded' | 'failed';
  payment_id?: string;
  seats?: string[];
  expires_at?: string;
  confirmed_at?: string;
  cancelled_at?: string;
  cancellation_reason?: string;
  seance?: Seance;
  user?: any;
  created_at: string;
}

class CoreService {
  // Catégories
  async getCategories(): Promise<Category[]> {
    const response = await fetch(`${API_CONFIG.CORE_URL}/public/categories`);
    const data: ApiResponse<Category[]> = await response.json();
    return data.data || [];
  }

  async getCategory(id: number): Promise<Category | null> {
    const response = await fetch(`${API_CONFIG.CORE_URL}/public/categories/${id}`);
    const data: ApiResponse<Category> = await response.json();
    return data.data || null;
  }

  // Spectacles
  async getSpectacles(params?: {
    category_id?: number;
    status?: string;
    is_published?: boolean;
    search?: string;
    page?: number;
    per_page?: number;
  }): Promise<PaginatedResponse<Spectacle>> {
    const queryParams = new URLSearchParams();
    if (params?.category_id) queryParams.append('category_id', params.category_id.toString());
    if (params?.status) queryParams.append('status', params.status);
    if (params?.is_published !== undefined) queryParams.append('is_published', params.is_published.toString());
    if (params?.search) queryParams.append('search', params.search);
    if (params?.page) queryParams.append('page', params.page.toString());
    if (params?.per_page) queryParams.append('per_page', params.per_page.toString());

    const response = await fetch(`${API_CONFIG.CORE_URL}/public/spectacles?${queryParams}`);
    return await response.json();
  }

  async getUpcomingSpectacles(): Promise<Spectacle[]> {
    const response = await fetch(`${API_CONFIG.CORE_URL}/public/spectacles/upcoming`);
    const data: ApiResponse<Spectacle[]> = await response.json();
    return data.data || [];
  }

  async getSpectacle(id: number): Promise<Spectacle | null> {
    const response = await fetch(`${API_CONFIG.CORE_URL}/public/spectacles/${id}`);
    const data: ApiResponse<Spectacle> = await response.json();
    return data.data || null;
  }

  // Séances
  async getSeances(params?: {
    spectacle_id?: number;
    hall_id?: number;
    status?: string;
    date_from?: string;
    date_to?: string;
    upcoming_only?: boolean;
    page?: number;
    per_page?: number;
  }): Promise<PaginatedResponse<Seance>> {
    const queryParams = new URLSearchParams();
    if (params?.spectacle_id) queryParams.append('spectacle_id', params.spectacle_id.toString());
    if (params?.hall_id) queryParams.append('hall_id', params.hall_id.toString());
    if (params?.status) queryParams.append('status', params.status);
    if (params?.date_from) queryParams.append('date_from', params.date_from);
    if (params?.date_to) queryParams.append('date_to', params.date_to);
    if (params?.upcoming_only) queryParams.append('upcoming_only', 'true');
    if (params?.page) queryParams.append('page', params.page.toString());
    if (params?.per_page) queryParams.append('per_page', params.per_page.toString());

    const response = await fetch(`${API_CONFIG.CORE_URL}/public/seances?${queryParams}`);
    return await response.json();
  }

  async getSeance(id: number): Promise<Seance | null> {
    const response = await fetch(`${API_CONFIG.CORE_URL}/public/seances/${id}`);
    const data: ApiResponse<Seance> = await response.json();
    return data.data || null;
  }

  async getAvailableSeats(seanceId: number): Promise<{
    total_seats: number;
    booked_seats: number;
    remaining_seats: number;
    is_available: boolean;
  } | null> {
    const response = await fetch(`${API_CONFIG.CORE_URL}/public/seances/${seanceId}/available-seats`);
    const data = await response.json();
    return data.data || null;
  }

  // Réservations (authentification requise)
  async createReservation(data: {
    user_id: number;
    seance_id: number;
    quantity: number;
    seats?: string[];
  }): Promise<Reservation> {
    const response = await fetchWithAuth(`${API_CONFIG.CORE_URL}/reservations`, {
      method: 'POST',
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Erreur lors de la réservation');
    }

    const result: ApiResponse<Reservation> = await response.json();
    if (!result.data) throw new Error('Erreur lors de la réservation');
    return result.data;
  }

  async getUserReservations(userId: number): Promise<Reservation[]> {
    const response = await fetchWithAuth(`${API_CONFIG.CORE_URL}/users/${userId}/reservations`);
    const data: ApiResponse<Reservation[]> = await response.json();
    return data.data || [];
  }

  async getReservation(id: number): Promise<Reservation | null> {
    const response = await fetchWithAuth(`${API_CONFIG.CORE_URL}/reservations/${id}`);
    const data: ApiResponse<Reservation> = await response.json();
    return data.data || null;
  }

  async getReservationByReference(reference: string): Promise<Reservation | null> {
    const response = await fetch(`${API_CONFIG.CORE_URL}/public/reservations/reference/${reference}`);
    const data: ApiResponse<Reservation> = await response.json();
    return data.data || null;
  }

  async cancelReservation(id: number, reason?: string): Promise<void> {
    const response = await fetchWithAuth(`${API_CONFIG.CORE_URL}/reservations/${id}/cancel`, {
      method: 'POST',
      body: JSON.stringify({ cancellation_reason: reason }),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Erreur lors de l\'annulation');
    }
  }

  async initiatePayment(reservationId: number, customerEmail: string): Promise<{
    reservation: Reservation;
    payment: any;
    client_secret: string;
  }> {
    const response = await fetchWithAuth(
      `${API_CONFIG.CORE_URL}/reservations/${reservationId}/initiate-payment`,
      {
        method: 'POST',
        body: JSON.stringify({ customer_email: customerEmail }),
      }
    );

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Erreur lors de l\'initialisation du paiement');
    }

    const result: ApiResponse<any> = await response.json();
    if (!result.data) throw new Error('Erreur lors de l\'initialisation du paiement');
    return result.data;
  }
}

export const coreService = new CoreService();
