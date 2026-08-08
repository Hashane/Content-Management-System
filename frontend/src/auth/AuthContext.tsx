import { createContext, useContext, type ReactNode } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { AuthUser } from '../types';
import { fetchMe, login as loginRequest, logout as logoutRequest } from '../api/authApi';

interface AuthContextValue {
  user: AuthUser | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  can: (privilege: string) => boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();

  const { data: user, isLoading } = useQuery({
    queryKey: ['auth-user'],
    queryFn: () => fetchMe().catch(() => null),
    staleTime: Infinity,
    retry: false,
  });

  const loginMutation = useMutation({
    mutationFn: ({ email, password }: { email: string; password: string }) => loginRequest(email, password),
    onSuccess: (authedUser) => queryClient.setQueryData(['auth-user'], authedUser),
  });

  const logoutMutation = useMutation({
    mutationFn: logoutRequest,
    onSuccess: () => queryClient.setQueryData(['auth-user'], null),
  });

  async function login(email: string, password: string) {
    await loginMutation.mutateAsync({ email, password });
  }

  async function logout() {
    await logoutMutation.mutateAsync();
  }

  function can(privilege: string) {
    return user?.privileges.includes(privilege) ?? false;
  }

  return (
    <AuthContext.Provider value={{ user: user ?? null, isLoading, login, logout, can }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }

  return context;
}
