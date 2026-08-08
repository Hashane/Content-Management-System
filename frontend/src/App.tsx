import { Routes, Route } from 'react-router-dom';
import { HomePage } from './pages/HomePage';
import { PageView } from './pages/PageView';
import { LoginPage } from './pages/admin/LoginPage';
import { AdminLayout } from './pages/admin/AdminLayout';
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
          <Route path="pages" element={<p>Pages admin — coming next.</p>} />
          <Route path="menu" element={<p>Menu admin — coming next.</p>} />
        </Route>
      </Route>
    </Routes>
  );
}

export default App;
