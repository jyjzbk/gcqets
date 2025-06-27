<template>
  <div class="district-map">
    <div class="map-toolbar">
      <el-button-group>
        <el-button 
          :type="viewMode === 'schools' ? 'primary' : 'default'"
          @click="viewMode = 'schools'"
          size="small"
        >
          <el-icon><School /></el-icon>
          学校分布
        </el-button>
        <el-button 
          :type="viewMode === 'districts' ? 'primary' : 'default'"
          @click="viewMode = 'districts'"
          size="small"
        >
          <el-icon><MapLocation /></el-icon>
          学区边界
        </el-button>
        <el-button 
          :type="viewMode === 'analysis' ? 'primary' : 'default'"
          @click="viewMode = 'analysis'"
          size="small"
        >
          <el-icon><DataAnalysis /></el-icon>
          分析视图
        </el-button>
      </el-button-group>
      
      <div class="map-controls">
        <el-button @click="refreshMapData" :loading="loading" size="small">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <el-button @click="resetMapView" size="small">
          <el-icon><Aim /></el-icon>
          重置视图
        </el-button>
      </div>
    </div>
    
    <div 
      ref="mapContainer" 
      class="map-container"
      v-loading="loading"
      element-loading-text="加载地图数据中..."
    ></div>
    
    <div class="map-legend" v-if="legendData.length > 0">
      <div class="legend-title">图例</div>
      <div class="legend-items">
        <div 
          v-for="item in legendData" 
          :key="item.name"
          class="legend-item"
        >
          <div 
            class="legend-color" 
            :style="{ backgroundColor: item.color }"
          ></div>
          <span class="legend-text">{{ item.name }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import * as echarts from 'echarts'
import { districtApi } from '@/api/district'
import { ElMessage } from 'element-plus'

const props = defineProps({
  selectedDistrict: {
    type: Object,
    default: null
  },
  regionId: {
    type: [String, Number],
    default: null
  }
})

const emit = defineEmits(['school-click', 'district-click'])

// 响应式数据
const loading = ref(false)
const mapContainer = ref(null)
const viewMode = ref('schools') // schools, districts, analysis
const chartInstance = ref(null)

// 地图数据
const schoolLocations = ref([])
const districtBoundaries = ref([])
const legendData = ref([])

// 地图配置
const mapOptions = ref({
  title: {
    text: '学区划分地图',
    left: 'center',
    textStyle: {
      fontSize: 16,
      fontWeight: 'bold'
    }
  },
  tooltip: {
    trigger: 'item',
    formatter: function(params) {
      if (params.componentType === 'scatter') {
        const data = params.data
        return `
          <div>
            <strong>${data.name}</strong><br/>
            学校编码: ${data.code || '未设置'}<br/>
            学生数量: ${data.student_count || 0}人<br/>
            所属学区: ${data.district_name || '未分配'}<br/>
            地址: ${data.address || '未设置'}
          </div>
        `
      } else if (params.componentType === 'lines') {
        return `学区边界: ${params.data.name}`
      }
      return params.name
    }
  },
  legend: {
    show: false
  },
  geo: {
    map: 'china',
    roam: true,
    zoom: 1.2,
    center: [116.4074, 39.9042], // 默认北京中心
    itemStyle: {
      areaColor: '#f3f3f3',
      borderColor: '#999',
      borderWidth: 0.5
    },
    emphasis: {
      itemStyle: {
        areaColor: '#e6f7ff'
      }
    }
  },
  series: []
})

// 颜色配置
const colors = [
  '#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de',
  '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc', '#ff9f7f'
]

// 方法
const initMap = async () => {
  if (!mapContainer.value) return
  
  try {
    chartInstance.value = echarts.init(mapContainer.value)
    chartInstance.value.setOption(mapOptions.value)
    
    // 绑定点击事件
    chartInstance.value.on('click', handleMapClick)
    
    // 加载地图数据
    await refreshMapData()
  } catch (error) {
    console.error('初始化地图失败:', error)
    ElMessage.error('初始化地图失败')
  }
}

const refreshMapData = async () => {
  loading.value = true
  try {
    await Promise.all([
      loadSchoolLocations(),
      loadDistrictBoundaries()
    ])
    updateMapView()
  } catch (error) {
    console.error('刷新地图数据失败:', error)
    ElMessage.error('刷新地图数据失败')
  } finally {
    loading.value = false
  }
}

const loadSchoolLocations = async () => {
  try {
    const params = {}
    if (props.regionId) {
      params.region_id = props.regionId
    }
    
    const response = await districtApi.getSchoolLocations(params)
    schoolLocations.value = response.data.data || []
  } catch (error) {
    console.error('加载学校位置失败:', error)
  }
}

const loadDistrictBoundaries = async () => {
  try {
    const params = {}
    if (props.regionId) {
      params.region_id = props.regionId
    }
    
    const response = await districtApi.getDistrictBoundaries(params)
    districtBoundaries.value = response.data.data || []
  } catch (error) {
    console.error('加载学区边界失败:', error)
  }
}

const updateMapView = () => {
  if (!chartInstance.value) return
  
  const series = []
  legendData.value = []
  
  if (viewMode.value === 'schools' || viewMode.value === 'analysis') {
    // 显示学校分布
    const schoolData = schoolLocations.value
      .filter(school => school.latitude && school.longitude)
      .map(school => ({
        name: school.organization?.name || '未知学校',
        value: [school.longitude, school.latitude],
        code: school.organization?.code,
        student_count: school.student_count,
        district_name: school.organization?.parent?.name,
        address: school.full_address,
        symbolSize: Math.max(8, Math.min(20, (school.student_count || 100) / 50))
      }))
    
    series.push({
      name: '学校分布',
      type: 'scatter',
      coordinateSystem: 'geo',
      data: schoolData,
      symbol: 'circle',
      itemStyle: {
        color: '#5470c6'
      },
      emphasis: {
        itemStyle: {
          color: '#ff6b6b'
        }
      }
    })
    
    legendData.value.push({
      name: '学校位置',
      color: '#5470c6'
    })
  }
  
  if (viewMode.value === 'districts' || viewMode.value === 'analysis') {
    // 显示学区边界（简化显示）
    districtBoundaries.value.forEach((boundary, index) => {
      if (boundary.boundary_points && boundary.boundary_points.length > 0) {
        const color = colors[index % colors.length]
        
        // 创建边界线数据
        const boundaryLines = boundary.boundary_points.map((point, i) => {
          const nextPoint = boundary.boundary_points[(i + 1) % boundary.boundary_points.length]
          return {
            coords: [
              [point.lng, point.lat],
              [nextPoint.lng, nextPoint.lat]
            ]
          }
        })
        
        series.push({
          name: boundary.name,
          type: 'lines',
          coordinateSystem: 'geo',
          data: boundaryLines,
          lineStyle: {
            color: color,
            width: 2,
            opacity: 0.8
          },
          emphasis: {
            lineStyle: {
              width: 3,
              opacity: 1
            }
          }
        })
        
        legendData.value.push({
          name: boundary.name,
          color: color
        })
      }
    })
  }
  
  const newOptions = {
    ...mapOptions.value,
    series: series
  }
  
  chartInstance.value.setOption(newOptions, true)
}

const handleMapClick = (params) => {
  if (params.componentType === 'scatter') {
    // 点击学校
    emit('school-click', params.data)
  } else if (params.componentType === 'lines') {
    // 点击学区边界
    emit('district-click', params.data)
  }
}

const resetMapView = () => {
  if (chartInstance.value) {
    chartInstance.value.dispatchAction({
      type: 'restore'
    })
  }
}

// 监听视图模式变化
watch(viewMode, () => {
  updateMapView()
})

// 监听选中学区变化
watch(() => props.selectedDistrict, () => {
  if (props.selectedDistrict) {
    // 可以高亮显示选中的学区
    updateMapView()
  }
})

// 监听区域变化
watch(() => props.regionId, () => {
  refreshMapData()
})

// 生命周期
onMounted(async () => {
  await nextTick()
  initMap()
})

onUnmounted(() => {
  if (chartInstance.value) {
    chartInstance.value.dispose()
  }
})
</script>

<style scoped>
.district-map {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 6px;
  overflow: hidden;
}

.map-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f5f7fa;
  border-bottom: 1px solid #e4e7ed;
}

.map-controls {
  display: flex;
  gap: 8px;
}

.map-container {
  flex: 1;
  min-height: 400px;
  position: relative;
}

.map-legend {
  position: absolute;
  top: 60px;
  right: 20px;
  background: rgba(255, 255, 255, 0.9);
  padding: 12px;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(4px);
}

.legend-title {
  font-weight: 600;
  margin-bottom: 8px;
  color: #303133;
  font-size: 14px;
}

.legend-items {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  flex-shrink: 0;
}

.legend-text {
  font-size: 12px;
  color: #606266;
}
</style>
