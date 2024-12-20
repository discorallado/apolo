const colors = require('tailwindcss/colors')

module.exports = {
    content: ['./app/Filament/**/*.php', './resources/**/*.blade.php'],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                danger: colors.rose,
                primary: colors.blue,
                secondary: colors.gray,
                success: colors.green,
                warning: colors.yellow,
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
