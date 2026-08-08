import { useQuery } from '@tanstack/react-query';
import { fetchMenu } from '../api/publicApi';
import { MenuTree } from '../components/MenuTree';

export function HomePage() {
  const { data: menu, isLoading, isError } = useQuery({
    queryKey: ['public-menu'],
    queryFn: fetchMenu,
  });

  if (isLoading) return <p>Loading menu…</p>;
  if (isError) return <p>Something went wrong loading the menu.</p>;
  if (!menu || menu.length === 0) return <p>No pages published yet.</p>;

  return (
    <div>
      <h1>Pages</h1>
      <MenuTree items={menu} />
    </div>
  );
}