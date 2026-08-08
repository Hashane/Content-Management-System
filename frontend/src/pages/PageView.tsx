import { useParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import DOMPurify from 'dompurify';
import { fetchPageBySlug } from '../api/publicApi';

export function PageView() {
  const { slug } = useParams<{ slug: string }>();

  const { data: page, isLoading, isError } = useQuery({
    queryKey: ['public-page', slug],
    queryFn: () => fetchPageBySlug(slug as string),
    enabled: Boolean(slug),
  });

  if (isLoading) return <p>Loading page…</p>;
  if (isError || !page) return <p>Page not found.</p>;

  const safeHtml = DOMPurify.sanitize(page.body_html);

  return (
    <article>
      <p>
        <Link to="/">&larr; Back</Link>
      </p>

      <h1>{page.title}</h1>

      {page.cover_image_url && (
        <img src={page.cover_image_url} alt={page.title} className="cover-image" />
      )}

      <div dangerouslySetInnerHTML={{ __html: safeHtml }} />
    </article>
  );
}
