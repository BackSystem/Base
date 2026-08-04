export {}

declare global {
    interface Number {
        pad(length?: number): string
        sizeFormat(decimals?: number): string
    }
}

Number.prototype.pad = function (length: number = 2): string {
    return String(this.valueOf()).padStart(length, '0')
}

Number.prototype.sizeFormat = function (decimals: number = 2): string {
    const value = this.valueOf()

    if (value === 0) {
        return `0 ${['o'][0]}`
    }

    const units = ['o', 'ko', 'Mo', 'Go', 'To']
    const index = Math.min(
        Math.floor(Math.log(Math.abs(value)) / Math.log(1024)),
        units.length - 1
    )

    return (
        (value / Math.pow(1024, index))
            .toFixed(decimals)
            .replace('.', ',') +
        ` ${units[index]}`
    )
}
