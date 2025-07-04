// 图标检查工具
import * as Icons from '@element-plus/icons-vue'

/**
 * 检查图标是否存在
 */
export function checkIcon(iconName) {
  return iconName in Icons
}

/**
 * 获取所有可用图标
 */
export function getAllIcons() {
  return Object.keys(Icons)
}

/**
 * 检查项目中使用的图标
 */
export function checkProjectIcons() {
  const usedIcons = [
    // 菜单图标
    'Operation',
    'Calendar', 
    'EditPen',
    'Select',
    'Tools',
    'Grid',
    
    // 审核相关图标
    'Search',
    'Refresh',
    'Check',
    'Close',
    'RefreshLeft',
    'ArrowDown',
    'Picture',
    
    // 审核日志图标
    'Document',
    'Warning',
    'Setting',
    'Cpu', // 替代 Robot
    'User',
    'Timer',
    
    // 系统测试图标
    'VideoPlay',
    'Delete',
    'Loading',
    'CircleCheck',
    'CircleClose',
    'Minus',
    
    // 其他常用图标
    'Monitor',
    'OfficeBuilding',
    'Key',
    'School',
    'DataAnalysis',
    'Reading',
    'Box',
    'Goods',
    'TrendCharts',
    'DataLine'
  ]
  
  const results = {
    valid: [],
    invalid: [],
    total: usedIcons.length
  }
  
  usedIcons.forEach(iconName => {
    if (checkIcon(iconName)) {
      results.valid.push(iconName)
    } else {
      results.invalid.push(iconName)
    }
  })
  
  return results
}

/**
 * 打印图标检查结果
 */
export function printIconCheckResults() {
  const results = checkProjectIcons()
  
  console.log('=== 图标检查结果 ===')
  console.log(`总计: ${results.total}`)
  console.log(`有效: ${results.valid.length}`)
  console.log(`无效: ${results.invalid.length}`)
  
  if (results.invalid.length > 0) {
    console.warn('无效图标:', results.invalid)
  } else {
    console.log('✅ 所有图标都有效!')
  }
  
  return results
}

/**
 * 建议替代图标
 */
export function suggestAlternativeIcons(invalidIcons) {
  const suggestions = {
    'Robot': ['Cpu', 'Monitor', 'Setting'],
    'AI': ['Cpu', 'Monitor', 'Magic'],
    'Artificial': ['Cpu', 'Monitor'],
    'Machine': ['Cpu', 'Monitor', 'Setting']
  }
  
  const result = {}
  
  invalidIcons.forEach(icon => {
    if (suggestions[icon]) {
      result[icon] = suggestions[icon].filter(alt => checkIcon(alt))
    } else {
      // 尝试找到相似的图标
      const similar = getAllIcons().filter(availableIcon => 
        availableIcon.toLowerCase().includes(icon.toLowerCase()) ||
        icon.toLowerCase().includes(availableIcon.toLowerCase())
      )
      result[icon] = similar.slice(0, 3) // 最多3个建议
    }
  })
  
  return result
}

// 自动运行检查（仅在开发环境）
if (import.meta.env.DEV) {
  printIconCheckResults()
}
