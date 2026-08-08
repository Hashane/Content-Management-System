import { Link } from 'react-router-dom';
import type { MenuItemNode } from '../types';

export function MenuTree({ items }: { items: MenuItemNode[] }) {
  return (
    <div className="menu-index">
      {items.map((item) =>
        item.item_type === 'page' && item.page ? (
          <Link key={item.id} to={`/pages/${item.page.slug}`} className="menu-entry">
            {item.label}
          </Link>
        ) : (
          <section key={item.id} className="menu-section">
            <h2 className="menu-eyebrow">{item.label}</h2>
            {item.children.length > 0 && <MenuTree items={item.children} />}
          </section>
        ),
      )}
    </div>
  );
}
