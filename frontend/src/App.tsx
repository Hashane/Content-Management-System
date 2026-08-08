import { Routes, Route } from 'react-router-dom';
import { HomePage } from './pages/HomePage';
import { PageView } from './pages/PageView';
import './App.css';

function App() {
  return (
    <Routes>
      <Route path="/" element={<HomePage />} />
      <Route path="/pages/:slug" element={<PageView />} />
    </Routes>
  );
}

export default App;
