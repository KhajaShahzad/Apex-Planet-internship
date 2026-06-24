import React from 'react';

// Custom SVG Line Chart
export function OrdersLineChart({ data }) {
  if (!data || data.length === 0) {
    return (
      <div style={{ height: 200, display: 'flex', alignItems: 'center', justify: 'center', color: 'var(--text-muted)' }}>
        No data available
      </div>
    );
  }

  const width = 500;
  const height = 200;
  const padding = 30;
  
  const maxVal = Math.max(...data.map(d => d.count), 4);
  const minVal = 0;
  
  const getX = (idx) => padding + (idx * (width - padding * 2) / (data.length - 1 || 1));
  const getY = (val) => height - padding - ((val - minVal) * (height - padding * 2) / (maxVal - minVal));

  // Build SVG Path
  let pathD = "";
  let areaD = "";
  
  data.forEach((d, idx) => {
    const x = getX(idx);
    const y = getY(d.count);
    if (idx === 0) {
      pathD = `M ${x} ${y}`;
      areaD = `M ${x} ${height - padding} L ${x} ${y}`;
    } else {
      pathD += ` L ${x} ${y}`;
      areaD += ` L ${x} ${y}`;
    }
    if (idx === data.length - 1) {
      areaD += ` L ${x} ${height - padding} Z`;
    }
  });

  return (
    <div style={{ width: '100%' }}>
      <svg viewBox={`0 0 ${width} ${height}`} style={{ width: '100%', height: 'auto', overflow: 'visible' }}>
        <defs>
          <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor="var(--primary)" stopOpacity="0.4"/>
            <stop offset="100%" stopColor="var(--primary)" stopOpacity="0.0"/>
          </linearGradient>
        </defs>
        
        {/* Grid Lines */}
        {[0, 0.25, 0.5, 0.75, 1].map((ratio, idx) => {
          const y = padding + ratio * (height - padding * 2);
          const gridVal = Math.round(maxVal - ratio * (maxVal - minVal));
          return (
            <g key={idx}>
              <line 
                x1={padding} 
                y1={y} 
                x2={width - padding} 
                y2={y} 
                stroke="rgba(255,255,255,0.05)" 
                strokeDasharray="4 4" 
              />
              <text 
                x={padding - 8} 
                y={y + 4} 
                fill="var(--text-muted)" 
                fontSize="9" 
                textAnchor="end"
              >
                {gridVal}
              </text>
            </g>
          );
        })}

        {/* Shaded Area under path */}
        {data.length > 0 && <path d={areaD} fill="url(#chartGradient)" />}

        {/* Main Line */}
        {data.length > 0 && (
          <path 
            d={pathD} 
            fill="none" 
            stroke="var(--primary)" 
            strokeWidth="3" 
            strokeLinecap="round" 
            strokeLinejoin="round" 
            style={{ filter: 'drop-shadow(0px 0px 4px rgba(99, 102, 241, 0.5))' }}
          />
        )}

        {/* Data points */}
        {data.map((d, idx) => (
          <g key={idx}>
            <circle 
              cx={getX(idx)} 
              cy={getY(d.count)} 
              r="4" 
              fill="#ffffff" 
              stroke="var(--primary)" 
              strokeWidth="2" 
            />
            {/* Tooltip text on top of dot */}
            <text 
              x={getX(idx)} 
              y={getY(d.count) - 10} 
              fill="#ffffff" 
              fontSize="10" 
              fontWeight="bold" 
              textAnchor="middle"
            >
              {d.count}
            </text>
            {/* X Axis Label */}
            <text 
              x={getX(idx)} 
              y={height - 10} 
              fill="var(--text-muted)" 
              fontSize="9" 
              textAnchor="middle"
            >
              {d.date}
            </text>
          </g>
        ))}
      </svg>
    </div>
  );
}

// Custom SVG Donut Chart
export function CategoryDonutChart({ data }) {
  if (!data || data.length === 0) {
    return (
      <div style={{ height: 200, display: 'flex', alignItems: 'center', justify: 'center', color: 'var(--text-muted)' }}>
        No data available
      </div>
    );
  }

  const total = data.reduce((sum, item) => sum + item.amount, 0);
  const colors = [
    '#6366f1', // Indigo
    '#8b5cf6', // Violet
    '#d946ef', // Fuchsia
    '#10b981', // Emerald
    '#f59e0b', // Amber
    '#0ea5e9'  // Sky
  ];

  let accumulatedPercent = 0;

  const chartSegments = data.map((item, idx) => {
    const percent = total > 0 ? (item.amount / total) * 100 : 0;
    const strokeDash = `${percent} ${100 - percent}`;
    const strokeOffset = 100 - accumulatedPercent + 25; // start from 12 o'clock (+25)
    accumulatedPercent += percent;

    return {
      ...item,
      color: colors[idx % colors.length],
      percent: percent.toFixed(1),
      strokeDash,
      strokeOffset
    };
  });

  return (
    <div style={{ display: 'flex', alignItems: 'center', flexWrap: 'wrap', gap: '2rem', justifyContent: 'center' }}>
      {/* Circle Donut SVG */}
      <div style={{ position: 'relative', width: 160, height: 160 }}>
        <svg viewBox="0 0 36 36" style={{ width: '100%', height: '100%', transform: 'scaleY(-1)' }}>
          {/* Base Circle */}
          <circle 
            cx="18" 
            cy="18" 
            r="15.915" 
            fill="transparent" 
            stroke="rgba(255,255,255,0.03)" 
            strokeWidth="3.5" 
          />
          {/* Segments */}
          {chartSegments.map((seg, idx) => (
            <circle
              key={idx}
              cx="18"
              cy="18"
              r="15.915"
              fill="transparent"
              stroke={seg.color}
              strokeWidth="3.5"
              strokeDasharray={seg.strokeDash}
              strokeDashoffset={seg.strokeOffset}
              style={{
                transition: 'stroke-dashoffset 0.8s ease',
              }}
            />
          ))}
        </svg>
        {/* Center Label */}
        <div style={{
          position: 'absolute',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center'
        }}>
          <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 500 }}>Total Revenue</span>
          <span style={{ fontSize: '1.2rem', fontWeight: 800, color: '#ffffff', fontFamily: 'var(--font-display)' }}>
            ${total.toFixed(0)}
          </span>
        </div>
      </div>

      {/* Legend */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem', flexGrow: 1, minWidth: 160 }}>
        {chartSegments.map((seg, idx) => (
          <div key={idx} style={{ display: 'flex', alignItems: 'center', justify: 'between', gap: '1rem', fontSize: '0.85rem' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
              <span style={{ width: 10, height: 10, borderRadius: '50%', backgroundColor: seg.color, display: 'inline-block' }} />
              <span style={{ color: 'var(--text-main)', fontWeight: 500 }}>{seg.category}</span>
            </div>
            <div style={{ marginLeft: 'auto', textAlign: 'right' }}>
              <span style={{ color: '#ffffff', fontWeight: 700, marginRight: '0.5rem' }}>${seg.amount.toFixed(0)}</span>
              <span style={{ color: 'var(--text-muted)', fontSize: '0.75rem' }}>{seg.percent}%</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
