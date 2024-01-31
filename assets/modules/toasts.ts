import slideDown from '../functions/slideDown'

document.querySelectorAll('.toast.show').forEach((toast: HTMLDivElement) => {
    let timeout = setTimeout(() => {
        slideDown(toast)
    }, 5000)

    toast.addEventListener('click', () => {
        clearTimeout(timeout)

        slideDown(toast)
    })
})
