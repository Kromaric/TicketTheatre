import fetchWithAuth, { API_CONFIG, type ApiResponse } from './api';

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
  name: string; // Adapté au champ 'name' attendu par Laravel
  email: string;
  password: string;
  password_confirmation?: string;
}

export interface LoginResponse {
  success: boolean;
  token: string;
  user: User;
}

class AuthService {
  /**
   * Connexion via le Auth Service
   */
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    const response = await fetch(`${API_CONFIG.AUTH_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(credentials),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Erreur de connexion');
    }

    const data: LoginResponse = await response.json();

    this.setSession(data.token, data.user);
    return data;
  }

  /**
   * Inscription via le Core Service
   */
  async register(userData: RegisterData): Promise<any> {
    const response = await fetch(`${API_CONFIG.CORE_URL}/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json', // Évite les redirections HTML si erreur 422
      },
      body: JSON.stringify(userData),
    });

    const data = await response.json();

    if (!response.ok) {
      // Si c'est une erreur de validation (422), on peut extraire les messages précis
      if (response.status === 422 && data.errors) {
        const firstError = Object.values(data.errors)[0] as string[];
        throw new Error(firstError[0] || 'Données invalides');
      }
      throw new Error(data.message || 'Erreur lors de l\'inscription');
    }

    return data;
  }

  /**
   * Déconnexion
   */
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

  /**
   * Utilitaires de session
   */
  private setSession(token: string, user: User): void {
    localStorage.setItem('auth_token', token);
    localStorage.setItem('user', JSON.stringify(user));
  }

  async getCurrentUser(): Promise<User | null> {
    try {
      const response = await fetchWithAuth(`${API_CONFIG.AUTH_URL}/user`);
      if (!response.ok) return null;
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