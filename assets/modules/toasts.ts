import slideDown from '../functions/slideDown'

document.querySelectorAll('.toast.show').forEach((toast: HTMLDivElement) => {
    toast.addEventListener('click', () => {
        slideDown(toast)
    })
})
