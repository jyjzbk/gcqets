export function formatTreeData(data, { id = 'id', parentId = 'parent_id', children = 'children' } = {}) {
    const tree = []
    const map = {}
  
    data.forEach(item => {
      map[item[id]] = { ...item, [children]: [] }
    })
  
    data.forEach(item => {
      const node = map[item[id]]
      if (item[parentId] && map[item[parentId]]) {
        map[item[parentId]][children].push(node)
      } else {
        tree.push(node)
      }
    })
  
    return tree
  }
  
  export function flattenTree(tree, childrenKey = 'children') {
    const result = []
    const stack = [...tree]
  
    while (stack.length) {
      const node = stack.pop()
      const children = node[childrenKey]
      
      result.push({ ...node, [childrenKey]: undefined })
      
      if (children?.length) {
        stack.push(...children)
      }
    }
  
    return result
  }