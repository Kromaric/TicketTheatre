import { API_CONFIG, fetchWithAuth, type ApiResponse } from './api';

export interface User {
  id: number;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string;
  role: 'admin' | 'user';
  avatar?: string;
  is_active: boolean;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  first_name: string;
  last_name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface LoginResponse {
  success: boolean;
  token: string;
  user: User;
}

class AuthService {
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    const response = await fetch(`${API_CONFIG.AUTH_URL}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(credentials),
    });

    if (!response.ok) {
      const contentType = response.headers.get('content-type');
      if (contentType?.includes('application/json')) {
        const error = await response.json();
        throw new Error(error.message || 'Erreur de connexion');
      }
      throw new Error(`Erreur ${response.status}: ${response.statusText}`);
    }

    const data: LoginResponse = await response.json();
    
    localStorage.setItem('auth_token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));

    return data;
  }

  async register(userData: RegisterData): Promise<LoginResponse> {
    const response = await fetch(`${API_CONFIG.AUTH_URL}/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(userData),
    });

    if (!response.ok) {
      const contentType = response.headers.get('content-type');
      if (contentType?.includes('application/json')) {
        const error = await response.json();
        throw new Error(error.message || 'Erreur lors de l\'inscription');
      }
      throw new Error(`Erreur ${response.status}: ${response.statusText}`);
    }

    const data: LoginResponse = await response.json();
    
    localStorage.setItem('auth_token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));

    return data;
  }

  async logout(): Promise<void> {
    try {
      await fetchWithAuth(`${API_CONFIG.AUTH_URL}/logout`, {
        method: 'POST',
      });
    } finally {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
    }
  }

  async getCurrentUser(): Promise<User | null> {
    try {
      const response = await fetchWithAuth(`${API_CONFIG.AUTH_URL}/user`);
      
      if (!response.ok) {
        return null;
      }

      const contentType = response.headers.get('content-type');
      if (!contentType?.includes('application/json')) {
        return null;
      }

      const data: ApiResponse<User> = await response.json();
      return data.data || null;
    } catch {
      return null;
    }
  }

  getStoredUser(): User | null {
    const userStr = localStorage.getItem('user');
    if (!userStr) return null;
    
    try {
      return JSON.parse(userStr);
    } catch {
      return null;
    }
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }
}

export const authService = new AuthService();
