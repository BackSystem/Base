import slideDown from '@Base/functions/slideDown'

document.querySelectorAll('.toast.show').forEach((toast: HTMLDivElement) => {
    toast.addEventListener('click', () => {
        slideDown(toast)
    })
})