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
