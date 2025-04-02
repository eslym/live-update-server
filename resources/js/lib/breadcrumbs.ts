export function first_layer_dropdown(current: string) {
    return [
        {
            label: 'Projects',
            href: '/projects',
            active: current === 'projects'
        },
        {
            label: 'Accounts',
            href: '/accounts',
            active: current === 'accounts'
        },
        {
            label: 'API Tokens',
            href: '/tokens',
            active: current === 'tokens'
        },
        {
            label: 'Profile',
            href: '/profile',
            active: current === 'profile'
        }
    ];
}
