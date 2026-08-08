import { Routes, Route } from 'react-router-dom';
import { HomePage } from './pages/HomePage';
import { PageView } from './pages/PageView';
import { LoginPage } from './pages/admin/LoginPage';
import { AdminLayout } from './pages/admin/AdminLayout';
import { PagesListPage } from './pages/admin/PagesListPage';
import { PageFormPage } from './pages/admin/PageFormPage';
import { MenuBuilderPage } from './pages/admin/MenuBuilderPage';
import { RolePrivilegesPage } from './pages/admin/RolePrivilegesPage';
import { RequireAuth } from './auth/RequireAuth';
import './App.css';

function App() {
  return (
    <Routes>
      <Route path="/" element={<HomePage />} />
      <Route path="/pages/:slug" element={<PageView />} />

      <Route path="/admin/login" element={<LoginPage />} />
      <Route element={<RequireAuth />}>
        <Route path="/admin" element={<AdminLayout />}>
          <Route path="pages" element={<PagesListPage />} />
          <Route path="pages/new" element={<PageFormPage />} />
          <Route path="pages/:id/edit" element={<PageFormPage />} />
          <Route path="menu" element={<MenuBuilderPage />} />
          <Route path="roles" element={<RolePrivilegesPage />} />
        </Route>
      </Route>
    </Routes>
  );
}

export default App;
