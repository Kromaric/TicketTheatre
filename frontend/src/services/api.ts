// Configuration de l'API
export const API_CONFIG = {
  AUTH_URL: import.meta.env.VITE_AUTH_URL || 'http://localhost:8081/api',
  CORE_URL: import.meta.env.VITE_CORE_URL || 'http://localhost:8082/api',
  PAYMENT_URL: import.meta.env.VITE_PAYMENT_URL || 'http://localhost:8083/api',
};

// Types de base
export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: T[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

// Configuration par défaut pour fetch
export const fetchWithAuth = async (url: string, options: RequestInit = {}) => {
  const token = localStorage.getItem('auth_token');
  
  console.log('fetchWithAuth - URL:', url);
  console.log('fetchWithAuth - Token:', token ? 'Présent' : 'Absent');
  
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    ...(options.headers || {}),
  };

  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
    console.log('fetchWithAuth - Authorization header ajouté');
  } else {
    console.warn('fetchWithAuth - Aucun token trouvé dans localStorage');
  }

  console.log('fetchWithAuth - Headers:', headers);

  const response = await fetch(url, {
    ...options,
    headers,
  });

  console.log('fetchWithAuth - Response status:', response.status);

  if (response.status === 401) {
    console.error('fetchWithAuth - 401 Non autorisé, nettoyage du token');
    // Token invalide ou expiré
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    window.location.href = '/login';
    throw new Error('Session expirée');
  }

  return response;
};
