import { Link } from 'react-router-dom';
import type { MenuItemNode } from '../types';

export function MenuTree({ items }: { items: MenuItemNode[] }) {
  return (
    <ul className="menu-tree">
      {items.map((item) => (
        <li key={item.id}>
          {item.item_type === 'page' && item.page ? (
            <Link to={`/pages/${item.page.slug}`}>{item.label}</Link>
          ) : (
            <span className="menu-group-label">{item.label}</span>
          )}

          {item.children.length > 0 && <MenuTree items={item.children} />}
        </li>
      ))}
    </ul>
  );
}
