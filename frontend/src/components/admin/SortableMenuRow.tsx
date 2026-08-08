import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import type { FlatMenuNode } from '../../types';

interface SortableMenuRowProps {
  node: FlatMenuNode;
  pageTitle: string | null;
  canManage: boolean;
  onDelete: () => void;
}

export function SortableMenuRow({ node, pageTitle, canManage, onDelete }: SortableMenuRowProps) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: node.id,
    disabled: !canManage,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    paddingLeft: node.depth * 30,
    opacity: isDragging ? 0.5 : 1,
  };

  return (
    <li ref={setNodeRef} style={style} className="menu-row">
      {canManage && (
        <span className="menu-drag-handle" {...attributes} {...listeners}>
          ⠿
        </span>
      )}
      <span className={node.item_type === 'group' ? 'menu-group-label' : undefined}>{node.label}</span>
      {pageTitle && <span className="menu-row-page">→ {pageTitle}</span>}
      {canManage && (
        <button type="button" onClick={onDelete} className="menu-row-delete">
          Delete
        </button>
      )}
    </li>
  );
}
