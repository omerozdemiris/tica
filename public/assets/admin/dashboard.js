window.AdminDashboard = (function () {
  function createBarOption(title, categories, values) {
    return {
      title: { text: title, left: 'center', textStyle: { fontSize: 12 } },
      tooltip: { trigger: 'axis' },
      xAxis: { type: 'category', data: categories, axisLabel: { interval: 0, rotate: 30 } },
      yAxis: { type: 'value' },
      series: [{ type: 'bar', data: values, itemStyle: { color: '#111827' }, animationDuration: 800 }]
    };
  }
  function createPieOption(title, data) {
    return {
      title: { text: title, left: 'center', textStyle: { fontSize: 12 } },
      tooltip: { trigger: 'item' },
      series: [
        {
          type: 'pie',
          radius: ['30%', '70%'],
          avoidLabelOverlap: false,
          itemStyle: { borderRadius: 6, borderColor: '#fff', borderWidth: 1 },
          label: { show: false, position: 'center' },
          emphasis: { label: { show: true, fontSize: 14, fontWeight: 'bold' } },
          labelLine: { show: false },
          data,
          animationDuration: 800
        }
      ]
    };
  }
  function createGaugeOption(title, value) {
    return {
      title: { text: title, left: 'center', textStyle: { fontSize: 12 } },
      series: [
        {
          type: 'gauge',
          progress: { show: true, width: 12 },
          axisLine: { lineStyle: { width: 12 } },
          axisTick: { show: false },
          splitLine: { show: false },
          axisLabel: { show: false },
          pointer: { show: true },
          anchor: { show: true, size: 6 },
          detail: { valueAnimation: true, formatter: '{value}' },
          data: [{ value }]
        }
      ]
    };
  }

  function init() {
    $.getJSON('/admin/dashboard/metrics', function (resp) {
      const d = resp?.data || {};
      // Category clicks
      const catNames = (d.categoryClicks || []).map(x => x.name);
      const catVals = (d.categoryClicks || []).map(x => x.click_count);
      echarts.init(document.getElementById('chart-category-clicks')).setOption(createBarOption('Kategori Tıklamaları (Top 10)', catNames, catVals));
      // Product clicks
      const prodNames = (d.productClicks || []).map(x => x.title);
      const prodVals = (d.productClicks || []).map(x => x.click_count);
      echarts.init(document.getElementById('chart-product-clicks')).setOption(createBarOption('Ürün Tıklamaları (Top 10)', prodNames, prodVals));
      // Visitors
      echarts.init(document.getElementById('chart-visitors')).setOption(createGaugeOption('Ziyaretçi (Genel)', d.visitorCount || 0));
      // Orders
      const orderCats = ['Yeni', 'Bekleyen', 'İptal', 'Tamam'];
      const orderVals = [d.orders?.new || 0, d.orders?.pending || 0, d.orders?.canceled || 0, d.orders?.completed || 0];
      echarts.init(document.getElementById('chart-orders')).setOption(createBarOption('Siparişler', orderCats, orderVals));
      // Products active vs inactive
      const prodPie = [
        { name: 'Aktif', value: d.productsActive || 0 },
        { name: 'Pasif', value: d.productsInactive || 0 },
      ];
      echarts.init(document.getElementById('chart-products-active')).setOption(createPieOption('Ürün Durumu', prodPie));
      // Category product counts
      const cpcNames = (d.categoryProductCounts || []).map(x => x.name);
      const cpcVals = (d.categoryProductCounts || []).map(x => x.count);
      echarts.init(document.getElementById('chart-category-products')).setOption(createBarOption('Kategori Başına Ürün', cpcNames, cpcVals));
      // Stock low/out
      echarts.init(document.getElementById('chart-stock-low')).setOption(createBarOption('Azalan Stok', ['Adet'], [d.lowStock || 0]));
      echarts.init(document.getElementById('chart-stock-out')).setOption(createBarOption('Tükenmiş Stok', ['Adet'], [d.outStock || 0]));
      // Resize on window
      const charts = [
        'chart-category-clicks','chart-product-clicks','chart-visitors','chart-orders',
        'chart-products-active','chart-category-products','chart-stock-low','chart-stock-out'
      ].map(id => echarts.getInstanceByDom(document.getElementById(id)));
      $(window).on('resize', function(){ charts.forEach(c => c && c.resize()); });
    });
  }

  return { init };
})(); 


