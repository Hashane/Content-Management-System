import { useQuery } from '@tanstack/react-query';
import { fetchMenu } from '../api/publicApi';
import { MenuTree } from '../components/MenuTree';
import { PublicHeader } from '../components/PublicHeader';

export function HomePage() {
  const { data: menu, isLoading, isError } = useQuery({
    queryKey: ['public-menu'],
    queryFn: fetchMenu,
  });

  return (
    <>
      <PublicHeader />
      <main className="public-main">
        {isLoading && <p className="public-status">Loading…</p>}
        {isError && <p className="public-status">Something went wrong loading the menu.</p>}
        {menu?.length === 0 && <p className="public-status">No pages published yet.</p>}
        {menu && menu.length > 0 && <MenuTree items={menu} />}
      </main>
    </>
  );
}
