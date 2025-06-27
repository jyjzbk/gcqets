const PREFIX = 'gcqets_'

export const storage = {
  set(key, value, expire = 0) {
    const data = {
      value,
      expire: expire ? Date.now() + expire : 0
    }
    localStorage.setItem(PREFIX + key, JSON.stringify(data))
  },

  get(key) {
    const data = localStorage.getItem(PREFIX + key)
    if (!data) return null

    try {
      const { value, expire } = JSON.parse(data)
      if (expire && expire < Date.now()) {
        this.remove(key)
        return null
      }
      return value
    } catch {
      return null
    }
  },

  remove(key) {
    localStorage.removeItem(PREFIX + key)
  },

  clear() {
    localStorage.clear()
  }
}