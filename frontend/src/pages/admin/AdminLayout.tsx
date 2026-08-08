import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../../auth/AuthContext';

export function AdminLayout() {
  const { user, logout } = useAuth();

  return (
    <div className="admin-shell">
      <aside className="admin-nav">
        <h2>CMS Admin</h2>
        <nav>
          <NavLink to="/admin/pages">Pages</NavLink>
          <NavLink to="/admin/menu">Menu</NavLink>
        </nav>
        <div className="admin-user">
          <span>{user?.name}</span>
          <button onClick={() => logout()}>Log out</button>
        </div>
      </aside>
      <main className="admin-content">
        <Outlet />
      </main>
    </div>
  );
}
