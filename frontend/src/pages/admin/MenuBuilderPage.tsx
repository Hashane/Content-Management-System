import { useMemo, useState, type SubmitEvent } from 'react';
import { DndContext, PointerSensor, useSensor, useSensors, type DragEndEvent } from '@dnd-kit/core';
import { SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { createMenuItem, deleteMenuItem, fetchMenuTree, moveMenuItem } from '../../api/menuApi';
import { fetchPages } from '../../api/pagesApi';
import { SortableMenuRow } from '../../components/admin/SortableMenuRow';
import { useAuth } from '../../auth/AuthContext';
import type { AdminMenuItemNode, FlatMenuNode, MenuItemType } from '../../types';

function flattenTree(nodes: AdminMenuItemNode[], depth = 0, parentId: number | null = null): FlatMenuNode[] {
  return nodes.flatMap((node) => [
    { id: node.id, label: node.label, item_type: node.item_type, page_id: node.page_id, depth, parentId },
    ...flattenTree(node.children, depth + 1, node.id),
  ]);
}

export function MenuBuilderPage() {
  const { can } = useAuth();
  const canManage = can('menus.manage');
  const queryClient = useQueryClient();

  const { data: tree, isLoading } = useQuery({ queryKey: ['admin-menu-tree'], queryFn: fetchMenuTree });
  const { data: pages } = useQuery({
    queryKey: ['admin-pages-for-menu'],
    queryFn: () => fetchPages({ per_page: 100 }),
  });

  const flatNodes = useMemo(() => flattenTree(tree ?? []), [tree]);

  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 5 } }));

  const moveMutation = useMutation({
    mutationFn: ({ id, parentId, position }: { id: number; parentId: number | null; position: number }) =>
      moveMenuItem(id, parentId, position),
    onSuccess: (updatedTree) => queryClient.setQueryData(['admin-menu-tree'], updatedTree),
  });

  const deleteMutation = useMutation({
    mutationFn: deleteMenuItem,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-menu-tree'] }),
  });

  const createMutation = useMutation({
    mutationFn: createMenuItem,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-menu-tree'] }),
  });

  function handleDragEnd(event: DragEndEvent) {
    const { active, over } = event;
    if (!over || active.id === over.id) return;

    const overNode = flatNodes.find((n) => n.id === over.id);
    if (!overNode) return;

    // Drop onto a group = become its child. Drop onto a page = become its sibling, right after it.
    const newParentId = overNode.item_type === 'group' ? overNode.id : overNode.parentId;
    const newSiblings = flatNodes.filter((n) => n.parentId === newParentId && n.id !== active.id);
    const newPosition =
      overNode.item_type === 'group' ? newSiblings.length : newSiblings.findIndex((n) => n.id === over.id) + 1;

    moveMutation.mutate({ id: Number(active.id), parentId: newParentId, position: newPosition });
  }

  const [label, setLabel] = useState('');
  const [itemType, setItemType] = useState<MenuItemType>('group');
  const [pageId, setPageId] = useState('');

  function handleAddItem(event: SubmitEvent<HTMLFormElement>) {
    event.preventDefault();
    createMutation.mutate(
      { label, item_type: itemType, page_id: itemType === 'page' ? Number(pageId) : null },
      {
        onSuccess: () => {
          setLabel('');
          setPageId('');
        },
      },
    );
  }

  if (isLoading) return <p>Loading…</p>;

  return (
    <div>
      <h1>Menu</h1>

      {canManage && (
        <form onSubmit={handleAddItem} className="menu-add-form">
          <input placeholder="Label" value={label} onChange={(e) => setLabel(e.target.value)} required />
          <select value={itemType} onChange={(e) => setItemType(e.target.value as MenuItemType)}>
            <option value="group">Group</option>
            <option value="page">Page</option>
          </select>
          {itemType === 'page' && (
            <select value={pageId} onChange={(e) => setPageId(e.target.value)} required>
              <option value="">Select a page…</option>
              {pages?.data.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.title}
                </option>
              ))}
            </select>
          )}
          <button type="submit" disabled={createMutation.isPending}>
            Add
          </button>
        </form>
      )}

      <DndContext sensors={sensors} onDragEnd={handleDragEnd}>
        <SortableContext items={flatNodes.map((n) => n.id)} strategy={verticalListSortingStrategy}>
          <ul className="menu-tree-editor">
            {flatNodes.map((node) => (
              <SortableMenuRow
                key={node.id}
                node={node}
                pageTitle={pages?.data.find((p) => p.id === node.page_id)?.title ?? null}
                canManage={canManage}
                onDelete={() => deleteMutation.mutate(node.id)}
              />
            ))}
          </ul>
        </SortableContext>
      </DndContext>
    </div>
  );
}
