export interface PublicPage {
  title: string;
  slug: string;
  body_html: string;
  cover_image_url: string | null;
  published_at: string | null;
}

export interface MenuItemNode {
  id: number;
  label: string;
  item_type: 'group' | 'page';
  page: { slug: string; title: string } | null;
  children: MenuItemNode[];
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  roles: string[];
  privileges: string[];
}

export type PageStatus = 'draft' | 'published';

export interface AdminPage {
  id: number;
  title: string;
  slug: string;
  body_html: string;
  cover_image_url: string | null;
  status: PageStatus;
  published_at: string | null;
  created_by: { id: number; name: string } | null;
  updated_by: { id: number; name: string } | null;
  deleted_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    total: number;
  };
}

export type MenuItemType = 'group' | 'page';

export interface AdminMenuItemNode {
  id: number;
  label: string;
  item_type: MenuItemType;
  page_id: number | null;
  position: number;
  children: AdminMenuItemNode[];
}
