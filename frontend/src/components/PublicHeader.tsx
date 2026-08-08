import { Link } from 'react-router-dom';

export function PublicHeader() {
  return (
    <header className="public-header">
      <Link to="/" className="public-wordmark">
        <span className="public-mark" aria-hidden="true" />
        Pages
      </Link>
    </header>
  );
}
