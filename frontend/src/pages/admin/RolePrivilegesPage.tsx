import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchRoles, syncRolePrivileges } from '../../api/rolesApi';
import { fetchPrivileges } from '../../api/privilegesApi';
import { useAuth } from '../../auth/AuthContext';
import type { AdminRole } from '../../types';

export function RolePrivilegesPage() {
  const { can } = useAuth();
  const canManage = can('roles.update');
  const queryClient = useQueryClient();

  const { data: roles, isLoading: rolesLoading, isError: rolesError } = useQuery({
    queryKey: ['admin-roles'],
    queryFn: fetchRoles,
  });
  const { data: privileges, isLoading: privilegesLoading } = useQuery({
    queryKey: ['admin-privileges'],
    queryFn: fetchPrivileges,
  });

  const syncMutation = useMutation({
    mutationFn: ({ roleId, nextPrivileges }: { roleId: number; nextPrivileges: string[] }) =>
      syncRolePrivileges(roleId, nextPrivileges),
    onSuccess: (updatedRole) => {
      queryClient.setQueryData<AdminRole[]>(['admin-roles'], (current) =>
        current?.map((role) => (role.id === updatedRole.id ? updatedRole : role)),
      );
    },
  });

  function toggle(role: AdminRole, privilegeName: string) {
    const nextPrivileges = role.privileges.includes(privilegeName)
      ? role.privileges.filter((name) => name !== privilegeName)
      : [...role.privileges, privilegeName];

    syncMutation.mutate({ roleId: role.id, nextPrivileges });
  }

  if (rolesLoading || privilegesLoading) return <p>Loading…</p>;
  if (rolesError || !roles || !privileges) return <p>You don&apos;t have access to this page.</p>;

  return (
    <div>
      <h1>Roles &amp; Privileges</h1>
      <table className="admin-table privilege-grid">
        <thead>
          <tr>
            <th>Privilege</th>
            {roles.map((role) => (
              <th key={role.id}>{role.name}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {privileges.map((privilege) => (
            <tr key={privilege.id}>
              <td>{privilege.name}</td>
              {roles.map((role) => (
                <td key={role.id} className="privilege-grid-cell">
                  <input
                    type="checkbox"
                    checked={role.privileges.includes(privilege.name)}
                    disabled={!canManage}
                    onChange={() => toggle(role, privilege.name)}
                  />
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
