import { API_CONFIG, fetchWithAuth, type PaginatedResponse, type ApiResponse } from './api';

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

// Helper pour parser les réponses JSON en gérant les erreurs HTML
async function parseResponse<T>(response: Response): Promise<T> {
  const text = await response.text();
  
  // Vérifier si c'est du HTML (erreur Laravel)
  if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
    console.error('Réponse HTML reçue au lieu de JSON:', text.substring(0, 200));
    throw new Error(`Erreur serveur (${response.status}): Réponse HTML au lieu de JSON`);
  }
  
  try {
    return JSON.parse(text);
  } catch (error) {
    console.error('Erreur parsing JSON:', text.substring(0, 200));
    throw new Error('Réponse invalide du serveur');
  }
}

class CoreService {
  async getCategories(): Promise<Category[]> {
    try {
      const response = await fetch(`${API_CONFIG.CORE_URL}/public/categories`);
      if (!response.ok) {
        console.error('Erreur API categories:', response.status);
        return [];
      }
      const data: ApiResponse<Category[]> = await parseResponse(response);
      return Array.isArray(data.data) ? data.data : [];
    } catch (error) {
      console.error('Erreur getCategories:', error);
      return [];
    }
  }

  async getCategory(id: number): Promise<Category | null> {
    try {
      const response = await fetch(`${API_CONFIG.CORE_URL}/public/categories/${id}`);
      if (!response.ok) return null;
      const data: ApiResponse<Category> = await parseResponse(response);
      return data.data || null;
    } catch (error) {
      console.error('Erreur getCategory:', error);
      return null;
    }
  }

  async getSpectacles(params?: {
    category_id?: number;
    status?: string;
    is_published?: boolean;
    search?: string;
    page?: number;
    per_page?: number;
  }): Promise<PaginatedResponse<Spectacle>> {
    try {
      const queryParams = new URLSearchParams();
      if (params?.category_id) queryParams.append('category_id', params.category_id.toString());
      if (params?.status) queryParams.append('status', params.status);
      if (params?.is_published !== undefined) queryParams.append('is_published', params.is_published.toString());
      if (params?.search) queryParams.append('search', params.search);
      if (params?.page) queryParams.append('page', params.page.toString());
      if (params?.per_page) queryParams.append('per_page', params.per_page.toString());

      const url = `${API_CONFIG.CORE_URL}/public/spectacles?${queryParams}`;
      console.log('Fetching spectacles from:', url);
      
      const response = await fetch(url);
      
      if (!response.ok) {
        console.error('Erreur API spectacles:', response.status);
        return { success: false, data: [] };
      }
      
      const result = await parseResponse<any>(response);
      console.log('Spectacles data RAW:', result);
      
      let spectaclesList: Spectacle[] = [];
      
      if (Array.isArray(result.data)) {
        spectaclesList = result.data;
      } else if (result.data && typeof result.data === 'object') {
        if (Array.isArray(result.data.data)) {
          spectaclesList = result.data.data;
        } else if (Array.isArray(result.data.items)) {
          spectaclesList = result.data.items;
        }
      }
      
      console.log('Spectacles extraits:', spectaclesList);
      
      return {
        success: result.success || true,
        data: spectaclesList,
        meta: result.meta || result.data?.meta
      };
    } catch (error) {
      console.error('Erreur getSpectacles:', error);
      return { success: false, data: [] };
    }
  }

  async getUpcomingSpectacles(): Promise<Spectacle[]> {
    try {
      const response = await fetch(`${API_CONFIG.CORE_URL}/public/spectacles/upcoming`);
      if (!response.ok) return [];
      const data: ApiResponse<Spectacle[]> = await parseResponse(response);
      return Array.isArray(data.data) ? data.data : [];
    } catch (error) {
      console.error('Erreur getUpcomingSpectacles:', error);
      return [];
    }
  }

  async getSpectacle(id: number): Promise<Spectacle | null> {
    try {
      const response = await fetch(`${API_CONFIG.CORE_URL}/public/spectacles/${id}`);
      if (!response.ok) return null;
      const data: ApiResponse<Spectacle> = await parseResponse(response);
      return data.data || null;
    } catch (error) {
      console.error('Erreur getSpectacle:', error);
      return null;
    }
  }

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
    try {
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
      if (!response.ok) return { success: false, data: [] };
      
      const result = await parseResponse<any>(response);
      
      let seancesList: Seance[] = [];
      if (Array.isArray(result.data)) {
        seancesList = result.data;
      } else if (result.data && typeof result.data === 'object') {
        if (Array.isArray(result.data.data)) {
          seancesList = result.data.data;
        } else if (Array.isArray(result.data.items)) {
          seancesList = result.data.items;
        }
      }
      
      return {
        success: result.success || true,
        data: seancesList,
        meta: result.meta || result.data?.meta
      };
    } catch (error) {
      console.error('Erreur getSeances:', error);
      return { success: false, data: [] };
    }
  }

  async getSeance(id: number): Promise<Seance | null> {
    try {
      const response = await fetch(`${API_CONFIG.CORE_URL}/public/seances/${id}`);
      if (!response.ok) {
        console.error('Erreur getSeance, status:', response.status);
        return null;
      }
      const data: ApiResponse<Seance> = await parseResponse(response);
      return data.data || null;
    } catch (error) {
      console.error('Erreur getSeance:', error);
      return null;
    }
  }

  async getAvailableSeats(seanceId: number): Promise<{
    total_seats: number;
    booked_seats: number;
    remaining_seats: number;
    is_available: boolean;
  } | null> {
    try {
      const response = await fetch(`${API_CONFIG.CORE_URL}/public/seances/${seanceId}/available-seats`);
      if (!response.ok) return null;
      const data = await parseResponse<any>(response);
      return data.data || null;
    } catch (error) {
      console.error('Erreur getAvailableSeats:', error);
      return null;
    }
  }

  async createReservation(data: {
    user_id: number;
    seance_id: number;
    quantity: number;
    seats?: string[];
  }): Promise<Reservation> {
    console.log('createReservation - URL:', `${API_CONFIG.CORE_URL}/reservations`);
    console.log('createReservation - Data:', data);
    
    const response = await fetchWithAuth(`${API_CONFIG.CORE_URL}/reservations`, {
      method: 'POST',
      body: JSON.stringify(data),
    });

    console.log('createReservation - Response status:', response.status);
    console.log('createReservation - Response headers:', response.headers);

    if (!response.ok) {
      const errorData = await parseResponse<any>(response);
      console.error('createReservation - Error:', errorData);
      throw new Error(errorData.message || 'Erreur lors de la réservation');
    }

    const result: ApiResponse<Reservation> = await parseResponse(response);
    console.log('createReservation - Result:', result);
    
    if (!result.data) throw new Error('Erreur lors de la réservation');
    return result.data;
  }

  async getUserReservations(userId: number): Promise<Reservation[]> {
    try {
      const response = await fetchWithAuth(`${API_CONFIG.CORE_URL}/users/${userId}/reservations`);
      if (!response.ok) return [];
      const data: ApiResponse<Reservation[]> = await parseResponse(response);
      return Array.isArray(data.data) ? data.data : [];
    } catch (error) {
      console.error('Erreur getUserReservations:', error);
      return [];
    }
  }

  async getReservation(id: number): Promise<Reservation | null> {
    try {
      const response = await fetchWithAuth(`${API_CONFIG.CORE_URL}/reservations/${id}`);
      if (!response.ok) return null;
      const data: ApiResponse<Reservation> = await parseResponse(response);
      return data.data || null;
    } catch (error) {
      console.error('Erreur getReservation:', error);
      return null;
    }
  }

  async getReservationByReference(reference: string): Promise<Reservation | null> {
    try {
      const response = await fetch(`${API_CONFIG.CORE_URL}/public/reservations/reference/${reference}`);
      if (!response.ok) return null;
      const data: ApiResponse<Reservation> = await parseResponse(response);
      return data.data || null;
    } catch (error) {
      console.error('Erreur getReservationByReference:', error);
      return null;
    }
  }

  async cancelReservation(id: number, reason?: string): Promise<void> {
    const response = await fetchWithAuth(`${API_CONFIG.CORE_URL}/reservations/${id}/cancel`, {
      method: 'POST',
      body: JSON.stringify({ cancellation_reason: reason }),
    });

    if (!response.ok) {
      const error = await parseResponse<any>(response);
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
      const error = await parseResponse<any>(response);
      throw new Error(error.message || 'Erreur lors de l\'initialisation du paiement');
    }

    const result: ApiResponse<any> = await parseResponse(response);
    if (!result.data) throw new Error('Erreur lors de l\'initialisation du paiement');
    return result.data;
  }
}

export const coreService = new CoreService();
