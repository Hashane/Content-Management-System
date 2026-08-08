import { useParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import DOMPurify from 'dompurify';
import { fetchPageBySlug } from '../api/publicApi';
import { PublicHeader } from '../components/PublicHeader';

export function PageView() {
  const { slug } = useParams<{ slug: string }>();

  const { data: page, isLoading, isError } = useQuery({
    queryKey: ['public-page', slug],
    queryFn: () => fetchPageBySlug(slug as string),
    enabled: Boolean(slug),
  });

  return (
    <>
      <PublicHeader />
      <main className="public-main">
        {isLoading && <p className="public-status">Loading…</p>}
        {!isLoading && (isError || !page) && <p className="public-status">Page not found.</p>}

        {page && (
          <article className="page-article">
            <Link to="/" className="page-back">
              &larr; Back to index
            </Link>

            <h1 className="page-title">{page.title}</h1>

            {page.published_at && (
              <p className="page-meta">
                {new Date(page.published_at).toLocaleDateString(undefined, {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric',
                })}
              </p>
            )}

            {page.cover_image_url && (
              <img src={page.cover_image_url} alt={page.title} className="cover-image" />
            )}

            <div className="page-body" dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(page.body_html) }} />
          </article>
        )}
      </main>
    </>
  );
}
