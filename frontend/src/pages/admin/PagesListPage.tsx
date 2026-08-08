import { useState } from 'react';
import { Link } from 'react-router-dom';
import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { deletePage, fetchPages } from '../../api/pagesApi';
import { useAuth } from '../../auth/AuthContext';
import type { PageStatus } from '../../types';

export function PagesListPage() {
  const { can } = useAuth();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState<PageStatus | ''>('');
  const [page, setPage] = useState(1);

  const { data, isLoading, isError } = useQuery({
    queryKey: ['admin-pages', { search, status, page }],
    queryFn: () => fetchPages({ search: search || undefined, status: status || undefined, page }),
    placeholderData: keepPreviousData,
  });

  const deleteMutation = useMutation({
    mutationFn: deletePage,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-pages'] }),
  });

  function handleDelete(id: number, title: string) {
    if (window.confirm(`Delete "${title}"?`)) {
      deleteMutation.mutate(id);
    }
  }

  return (
    <div>
      <div className="admin-toolbar">
        <h1>Pages</h1>
        {can('pages.create') && (
          <Link to="/admin/pages/new" className="button">
            New Page
          </Link>
        )}
      </div>

      <div className="admin-filters">
        <input
          type="search"
          placeholder="Search by title…"
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
        />
        <select
          value={status}
          onChange={(e) => {
            setStatus(e.target.value as PageStatus | '');
            setPage(1);
          }}
        >
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
        </select>
      </div>

      {isLoading && <p>Loading…</p>}
      {isError && <p>Something went wrong loading pages.</p>}

      {data && (
        <>
          <table className="admin-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Published</th>
                <th>Updated by</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {data.data.map((row) => (
                <tr key={row.id}>
                  <td>{row.title}</td>
                  <td>{row.status}</td>
                  <td>{row.published_at ? new Date(row.published_at).toLocaleDateString() : '—'}</td>
                  <td>{row.updated_by?.name ?? '—'}</td>
                  <td className="admin-table-actions">
                    {can('pages.update') && <Link to={`/admin/pages/${row.id}/edit`}>Edit</Link>}
                    {can('pages.delete') && (
                      <button onClick={() => handleDelete(row.id, row.title)} disabled={deleteMutation.isPending}>
                        Delete
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          <div className="admin-pagination">
            <button disabled={data.meta.current_page <= 1} onClick={() => setPage((p) => p - 1)}>
              Previous
            </button>
            <span>
              Page {data.meta.current_page} of {data.meta.last_page}
            </span>
            <button disabled={data.meta.current_page >= data.meta.last_page} onClick={() => setPage((p) => p + 1)}>
              Next
            </button>
          </div>
        </>
      )}
    </div>
  );
}
