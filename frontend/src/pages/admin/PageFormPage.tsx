import { useState, type SubmitEvent } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { createPage, fetchPage, updatePage, type PagePayload } from '../../api/pagesApi';
import { CKEditorField } from '../../components/admin/CKEditorField';
import type { AdminPage, PageStatus } from '../../types';

export function PageFormPage() {
  const { id } = useParams();
  const isEditing = Boolean(id);

  const { data: existingPage, isLoading } = useQuery({
    queryKey: ['admin-page', id],
    queryFn: () => fetchPage(Number(id)),
    enabled: isEditing,
  });

  if (isEditing && isLoading) {
    return <p>Loading…</p>;
  }

  return <PageForm id={id} isEditing={isEditing} existingPage={existingPage} />;
}

interface PageFormProps {
  id: string | undefined;
  isEditing: boolean;
  existingPage: AdminPage | undefined;
}

function PageForm({ id, isEditing, existingPage }: PageFormProps) {
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const [title, setTitle] = useState(existingPage?.title ?? '');
  const [bodyHtml, setBodyHtml] = useState(existingPage?.body_html ?? '');
  const [status, setStatus] = useState<PageStatus>(existingPage?.status ?? 'draft');
  const [publishedAt, setPublishedAt] = useState(existingPage?.published_at?.slice(0, 16) ?? '');
  const [coverImage, setCoverImage] = useState<File | null>(null);
  const [error, setError] = useState<string | null>(null);

  const saveMutation = useMutation({
    mutationFn: (payload: PagePayload) => (isEditing ? updatePage(Number(id), payload) : createPage(payload)),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-pages'] });
      navigate('/admin/pages');
    },
  });

  function handleSubmit(event: SubmitEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);

    saveMutation.mutate(
      { title, body_html: bodyHtml, status, published_at: publishedAt || null, cover_image: coverImage },
      { onError: () => setError('Could not save this page. Check the fields and try again.') },
    );
  }

  return (
    <form onSubmit={handleSubmit} className="page-form">
      <h1>{isEditing ? 'Edit Page' : 'New Page'}</h1>
      {error && <p className="form-error">{error}</p>}

      <label>
        Title
        <input value={title} onChange={(e) => setTitle(e.target.value)} required />
      </label>

      <label>
        Body
        <CKEditorField value={bodyHtml} onChange={setBodyHtml} />
      </label>

      <label>
        Cover image
        <input type="file" accept="image/*" onChange={(e) => setCoverImage(e.target.files?.[0] ?? null)} />
      </label>
      {existingPage?.cover_image_url && !coverImage && (
        <img src={existingPage.cover_image_url} alt="" className="cover-image" />
      )}

      <label>
        Status
        <select value={status} onChange={(e) => setStatus(e.target.value as PageStatus)}>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
        </select>
      </label>

      <label>
        Publish date (optional)
        <input type="datetime-local" value={publishedAt} onChange={(e) => setPublishedAt(e.target.value)} />
      </label>

      <button type="submit" disabled={saveMutation.isPending}>
        {saveMutation.isPending ? 'Saving…' : 'Save'}
      </button>
    </form>
  );
}
