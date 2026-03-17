<x-filament-panels::page>
    <style>
        /* Variables CSS para modo claro por defecto */
        :root {
            --bg-primary: rgba(255, 255, 255, 0.95);
            --bg-secondary: rgba(249, 250, 251, 0.8);
            --bg-tertiary: rgba(255, 255, 255, 0.9);
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-tertiary: #9ca3af;
            --border-primary: rgba(229, 231, 235, 0.6);
            --border-secondary: rgba(229, 231, 235, 0.4);
            --border-tertiary: rgba(229, 231, 235, 0.3);
            --accent-blue: #3b82f6;
            --accent-blue-light: #60a5fa;
            --accent-green: #10b981;
            --accent-green-dark: #059669;
            --accent-red: #ef4444;
            --accent-orange: #fb923c;
            --shadow-light: rgba(0, 0, 0, 0.04);
            --shadow-medium: rgba(0, 0, 0, 0.08);
            --shadow-strong: rgba(0, 0, 0, 0.12);
        }
        
        /* Forzar modo claro cuando no hay clase .dark */
        html:not(.dark) {
            --bg-primary: rgba(255, 255, 255, 0.95);
            --bg-secondary: rgba(249, 250, 251, 0.8);
            --bg-tertiary: rgba(255, 255, 255, 0.9);
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-tertiary: #9ca3af;
            --border-primary: rgba(229, 231, 235, 0.6);
            --border-secondary: rgba(229, 231, 235, 0.4);
            --border-tertiary: rgba(229, 231, 235, 0.3);
            --shadow-light: rgba(0, 0, 0, 0.04);
            --shadow-medium: rgba(0, 0, 0, 0.08);
            --shadow-strong: rgba(0, 0, 0, 0.12);
        }
        
        /* Modo oscuro - solo con clase .dark */
        .dark {
            --bg-primary: rgba(30, 41, 59, 0.95);
            --bg-secondary: rgba(51, 65, 85, 0.8);
            --bg-tertiary: rgba(30, 41, 59, 0.9);
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            --border-primary: rgba(71, 85, 105, 0.6);
            --border-secondary: rgba(71, 85, 105, 0.4);
            --border-tertiary: rgba(71, 85, 105, 0.3);
            --shadow-light: rgba(0, 0, 0, 0.25);
            --shadow-medium: rgba(0, 0, 0, 0.4);
            --shadow-strong: rgba(0, 0, 0, 0.6);
        }
        
        
        /* Forzar modo claro con clase .light */
        .light {
            --bg-primary: rgba(255, 255, 255, 0.95);
            --bg-secondary: rgba(249, 250, 251, 0.8);
            --bg-tertiary: rgba(255, 255, 255, 0.9);
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-tertiary: #9ca3af;
            --border-primary: rgba(229, 231, 235, 0.6);
            --border-secondary: rgba(229, 231, 235, 0.4);
            --border-tertiary: rgba(229, 231, 235, 0.3);
            --shadow-light: rgba(0, 0, 0, 0.04);
            --shadow-medium: rgba(0, 0, 0, 0.08);
            --shadow-strong: rgba(0, 0, 0, 0.12);
        }
        
        /* Estilos base para modo claro (forzado) */
        .menu-container {
            display: flex;
            gap: 24px;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .list-container {
            width: 320px;
            padding: 16px;
        }
        
        .list-header {
            margin-bottom: 24px;
        }
        
        .list-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .list-subtitle {
            font-size: 14px;
            color: #6b7280;
            font-weight: 400;
        }
        
        .items-wrapper {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            padding-right: 4px;
        }
        
        .items-wrapper::-webkit-scrollbar {
            width: 4px;
        }
        
        .items-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .items-wrapper::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 2px;
        }
        
        .menu-item {
            position: relative;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .menu-item:last-child {
            margin-bottom: 0;
        }
        
        .item-glass {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 24px;
            border: 1px solid rgba(229, 231, 235, 0.4);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .menu-item:hover .item-glass {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
            border-color: rgba(229, 231, 235, 0.6);
        }
        
        .menu-item.selected .item-glass {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(96, 165, 250, 0.1));
            border: 1px solid rgba(147, 197, 253, 0.4);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.15);
        }
        
        .item-content {
            position: relative;
            padding: 18px 20px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .item-text {
            flex: 1;
        }
        
        .item-name {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }
        
        .item-description {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.4;
            font-weight: 400;
        }
        
        .item-indicator {
            margin-left: 14px;
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            background: rgba(249, 250, 251, 0.9);
            border: 1px solid rgba(229, 231, 235, 0.3);
        }
        
        .menu-item:hover:not(.selected) .item-indicator {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(229, 231, 235, 0.6);
        }
        
        .menu-item.selected .item-indicator {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
            border: none;
        }
        
        .indicator-icon {
            width: 14px;
            height: 14px;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .menu-item:not(.selected) .indicator-icon {
            color: #d1d5db;
        }
        
        .menu-item:hover:not(.selected) .indicator-icon {
            color: #9ca3af;
        }
        
        .menu-item.selected .indicator-icon {
            color: white;
        }
        
        .panel-container {
            flex: 1;
            position: relative;
        }
        
        .empty-state {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .empty-icon {
            width: 96px;
            height: 96px;
            color: #e5e7eb;
            margin-bottom: 16px;
        }
        
        .empty-title {
            font-size: 20px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        .empty-text {
            font-size: 14px;
            color: #9ca3af;
        }
        
        .panel-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 32px;
            border: 1px solid rgba(229, 231, 235, 0.4);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
            height: 100%;
            overflow: hidden;
            animation: slideIn 0.6s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
        }
        
        .panel-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255, 255, 255, 0.3) 50%, 
                transparent 100%);
            z-index: 1;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        
        .panel-top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.02) 0%, 
                rgba(255, 255, 255, 0.05) 50%, 
                rgba(255, 255, 255, 0.02) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 10;
        }
        
        .panel-product-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .product-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        
        .product-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: -0.01em;
        }
        
        .product-status {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .close-button {
            width: 36px;
            height: 36px;
            border: none;
            background: rgba(249, 250, 251, 0.8);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            border: 1px solid rgba(229, 231, 235, 0.3);
            color: #6b7280;
        }
        
        .close-button:hover {
            background: rgba(255, 255, 255, 0.95);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            color: #374151;
        }
        
        .close-icon {
            width: 18px;
            height: 18px;
        }
        
        .panel-body {
            padding: 28px;
            margin-top: 60px;
            height: calc(100% - 60px);
            overflow-y: auto;
        }
        
        .panel-body::-webkit-scrollbar {
            width: 6px;
        }
        
        .panel-body::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .panel-body::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 3px;
        }
        
        /* Forzar modo oscuro con clase .dark */
        .dark {
            --bg-primary: rgba(30, 41, 59, 0.95);
            --bg-secondary: rgba(51, 65, 85, 0.8);
            --bg-tertiary: rgba(30, 41, 59, 0.9);
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            --border-primary: rgba(71, 85, 105, 0.6);
            --border-secondary: rgba(71, 85, 105, 0.4);
            --border-tertiary: rgba(71, 85, 105, 0.3);
            --shadow-light: rgba(0, 0, 0, 0.25);
            --shadow-medium: rgba(0, 0, 0, 0.4);
            --shadow-strong: rgba(0, 0, 0, 0.6);
        }
        
        .dark .item-glass {
            background: rgba(30, 41, 59, 0.95);
            border-color: rgba(71, 85, 105, 0.4);
        }
        
        .dark .item-name {
            color: #f1f5f9;
        }
        
        .dark .item-description {
            color: #cbd5e1;
        }
        
        .dark .panel-content {
            background: rgba(30, 41, 59, 0.95);
            border-color: rgba(71, 85, 105, 0.4);
        }
        
        .dark .panel-top-bar {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.01) 0%, 
                rgba(255, 255, 255, 0.03) 50%, 
                rgba(255, 255, 255, 0.01) 100%);
            border-color: rgba(71, 85, 105, 0.4);
        }
        
        .dark .product-name {
            color: #f1f5f9;
        }
        
        .dark .product-status {
            color: #cbd5e1;
        }
        
        .dark .close-button {
            background: rgba(51, 65, 85, 0.8);
            border-color: rgba(71, 85, 105, 0.3);
            color: #cbd5e1;
        }
        
        .dark .close-button:hover {
            background: rgba(30, 41, 59, 0.95);
            color: #f1f5f9;
        }
        
        /* Vista de mesa */
        .table-view-button {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border: none;
            background: linear-gradient(135deg, var(--accent-blue-light), var(--accent-blue));
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            z-index: 1000;
        }
        
        .table-view-button:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(59, 130, 246, 0.4);
        }
        
        .table-view-button svg {
            width: 24px;
            height: 24px;
        }
        
        .table-view-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: fadeIn 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        }
        
        .table-container {
            background: var(--bg-primary);
            border-radius: 32px;
            padding: 32px;
            max-width: 90vw;
            max-height: 90vh;
            overflow: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        
        .table-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }
        
        .table-close-button {
            width: 40px;
            height: 40px;
            border: none;
            background: var(--bg-secondary);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            border: 1px solid var(--border-tertiary);
            color: var(--text-secondary);
        }
        
        .table-close-button:hover {
            background: var(--bg-tertiary);
            transform: scale(1.05);
            color: var(--text-primary);
        }
        
        .table-close-button svg {
            width: 18px;
            height: 18px;
        }
        
        .table-layout {
            display: grid;
            grid-template-columns: repeat(11, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .table-shape {
            grid-column: span 11;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border-radius: 24px;
            padding: 20px;
            text-align: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 8px 32px rgba(139, 92, 246, 0.3);
            margin-bottom: 24px;
        }
        
        .seats-container {
            display: grid;
            grid-template-columns: repeat(11, 1fr);
            gap: 8px;
        }
        
        .seat {
            aspect-ratio: 1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
        }
        
        .seat.empty {
            background: var(--bg-secondary);
            border: 2px solid var(--border-primary);
            color: var(--text-secondary);
        }
        
        .seat.empty:hover {
            background: var(--bg-tertiary);
            border-color: var(--accent-blue);
            transform: scale(1.05);
        }
        
        .seat.occupied {
            background: linear-gradient(135deg, var(--accent-blue-light), var(--accent-blue));
            border: 2px solid var(--accent-blue);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .seat.occupied:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        /* Tooltip */
        .seat-tooltip {
            position: absolute;
            bottom: calc(100% + 12px);
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-primary);
            border: 1px solid var(--border-primary);
            border-radius: 16px;
            padding: 16px;
            min-width: 200px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            z-index: 1000;
        }
        
        .seat-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 8px solid transparent;
            border-top-color: var(--border-primary);
        }
        
        .seat-tooltip::before {
            content: '';
            position: absolute;
            top: calc(100% + 1px);
            left: 50%;
            transform: translateX(-50%);
            border: 7px solid transparent;
            border-top-color: var(--bg-primary);
        }
        
        .seat:hover .seat-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-4px);
        }
        
        .tooltip-header {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .tooltip-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .tooltip-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .tooltip-item svg {
            width: 14px;
            height: 14px;
            color: var(--accent-blue);
        }
        
        .table-legend {
            display: flex;
            gap: 24px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .legend-seat {
            width: 20px;
            height: 20px;
            border-radius: 6px;
        }
        
        .legend-seat.empty {
            background: var(--bg-secondary);
            border: 2px solid var(--border-primary);
        }
        
        .legend-seat.occupied {
            background: linear-gradient(135deg, var(--accent-blue-light), var(--accent-blue));
            border: 2px solid var(--accent-blue);
        }
        
        .list-header {
            margin-bottom: 24px;
        }
        
        .list-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-blue-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .list-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 400;
        }
        
        .items-wrapper {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            padding-right: 4px;
        }
        
        .items-wrapper::-webkit-scrollbar {
            width: 4px;
        }
        
        .items-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .items-wrapper::-webkit-scrollbar-thumb {
            background: var(--border-primary);
            border-radius: 2px;
        }
        
        .menu-item {
            position: relative;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .menu-item:last-child {
            margin-bottom: 0;
        }
        
        .item-glass {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--bg-primary);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 24px;
            border: 1px solid var(--border-secondary);
            box-shadow: 0 2px 15px var(--shadow-light);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .menu-item:hover .item-glass {
            background: var(--bg-primary);
            box-shadow: 0 8px 25px var(--shadow-medium);
            transform: translateY(-1px);
            border-color: var(--border-primary);
        }
        
        .menu-item.selected .item-glass {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(96, 165, 250, 0.1));
            border: 1px solid rgba(147, 197, 253, 0.4);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.15);
        }
        
        .dark .menu-item.selected .item-glass {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(96, 165, 250, 0.15));
            border: 1px solid rgba(147, 197, 253, 0.5);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.25);
        }
        
        .item-content {
            position: relative;
            padding: 18px 20px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .item-text {
            flex: 1;
        }
        
        .item-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }
        
        .item-description {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.4;
            font-weight: 400;
        }
        
        .tabs-container {
            background: var(--bg-primary);
            border-radius: 24px;
            padding: 8px;
            margin-bottom: 24px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-secondary);
            display: flex;
            gap: 4px;
            box-shadow: 0 4px 20px var(--shadow-light);
        }
        
        .tab-button {
            flex: 1;
            padding: 12px 20px;
            background: transparent;
            border: none;
            border-radius: 18px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
        }
        
        .tab-button:hover {
            color: var(--text-primary);
            background: var(--bg-secondary);
        }
        
        .tab-button.active {
            background: linear-gradient(135deg, var(--accent-blue-light), var(--accent-blue));
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .schedule-editor {
            background: var(--bg-secondary);
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 20px;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--border-secondary);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .schedule-editor:hover {
            border-color: var(--border-primary);
            box-shadow: 0 8px 25px var(--shadow-light);
        }
        
        .schedule-section-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            letter-spacing: -0.01em;
        }
        
        .time-slot-item {
            background: var(--bg-tertiary);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid var(--border-tertiary);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .time-slot-item:hover {
            border-color: var(--accent-blue);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08);
        }
        
        .time-input {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid var(--border-primary);
            background: var(--bg-primary);
            border-radius: 14px;
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            font-weight: 500;
        }
        
        .time-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            background: var(--bg-primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        .time-input:disabled {
            background: var(--bg-secondary);
            color: var(--text-tertiary);
            cursor: not-allowed;
        }
        
        .time-input::placeholder {
            color: var(--text-tertiary);
        }
        
        /* Fix for select element arrows */
        .time-input[type="time"]::-webkit-calendar-picker-indicator,
        .time-input[type="number"]::-webkit-inner-spin-button,
        .time-input[type="number"]::-webkit-outer-spin-button {
            opacity: 0;
            display: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        select.time-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 36px;
            cursor: pointer;
            text-indent: 0;
            text-shadow: none;
        }
        
        select.time-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            background: var(--bg-primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        /* Ocultar calendario en inputs type="date" */
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0;
            display: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        input[type="date"]::-webkit-inner-spin-button,
        input[type="date"]::-webkit-outer-spin-button {
            opacity: 0;
            display: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        input[type="date"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        
        /* Remove arrows from option elements */
        select.time-input option {
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 8px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        /* Webkit browsers specific fix */
        select.time-input::-webkit-calendar-picker-indicator,
        select.time-input::-webkit-search-decoration,
        select.time-input::-webkit-search-cancel-button,
        select.time-input::-webkit-search-results-button,
        select.time-input::-webkit-search-results-decoration {
            display: none;
            -webkit-appearance: none;
        }
        
        /* Firefox specific fix */
        select.time-input::-ms-expand {
            display: none;
        }
        
        /* IE/Edge specific fix */
        select.time-input::-ms-value {
            background: transparent;
            color: var(--text-primary);
        }
        
        .remove-button {
            padding: 8px 12px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            color: var(--accent-red);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .remove-button:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.3);
            transform: translateY(-1px);
        }
        
        .add-button {
            padding: 10px 16px;
            background: linear-gradient(135deg, var(--accent-blue-light), var(--accent-blue));
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 3px 10px rgba(59, 130, 246, 0.25);
        }
        
        .add-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35);
        }
        
        .date-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-primary);
            background: var(--bg-primary);
            border-radius: 14px;
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            font-weight: 500;
        }
        
        .date-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            background: var(--bg-primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        /* Remove calendar icon */
        .date-input::-webkit-calendar-picker-indicator {
            opacity: 0;
            cursor: pointer;
        }
        
        .date-input::-webkit-inner-spin-button,
        .date-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .date-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        .date-selector {
            background: var(--bg-tertiary);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border-tertiary);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .save-button {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--accent-green), var(--accent-green-dark));
            border: none;
            border-radius: 18px;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
            letter-spacing: -0.01em;
        }
        
        .save-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
        }
        
        .empty-state-text {
            color: var(--text-tertiary);
            font-size: 14px;
            text-align: center;
            padding: 32px;
            font-weight: 400;
        }
        
        .override-indicator {
            display: inline-block;
            padding: 4px 8px;
            background: rgba(251, 146, 60, 0.1);
            border: 1px solid rgba(251, 146, 60, 0.2);
            border-radius: 8px;
            color: var(--accent-orange);
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .item-indicator {
            margin-left: 14px;
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .menu-item:not(.selected) .item-indicator {
            background: var(--bg-secondary);
            border: 1px solid var(--border-tertiary);
        }
        
        .menu-item:hover:not(.selected) .item-indicator {
            background: var(--bg-tertiary);
            border-color: var(--border-primary);
        }
        
        .menu-item.selected .item-indicator {
            background: linear-gradient(135deg, var(--accent-blue-light), var(--accent-blue));
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
            border: none;
        }
        
        .indicator-icon {
            width: 14px;
            height: 14px;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .menu-item:not(.selected) .indicator-icon {
            color: var(--text-tertiary);
        }
        
        .menu-item:hover:not(.selected) .indicator-icon {
            color: var(--text-secondary);
        }
        
        .menu-item.selected .indicator-icon {
            color: white;
        }
        
        .panel-container {
            flex: 1;
            position: relative;
        }
        
        .empty-state {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .empty-icon {
            width: 96px;
            height: 96px;
            color: var(--text-tertiary);
            margin-bottom: 16px;
        }
        
        .empty-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .empty-text {
            font-size: 14px;
            color: var(--text-tertiary);
        }
        
        .panel-content {
            background: var(--bg-primary);
            border-radius: 32px;
            border: 1px solid var(--border-secondary);
            box-shadow: 0 8px 32px var(--shadow-light);
            height: 100%;
            overflow: hidden;
            animation: slideIn 0.6s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
        }
        
        .panel-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255, 255, 255, 0.3) 50%, 
                transparent 100%);
            z-index: 1;
        }
        
        .dark .panel-content::before {
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255, 255, 255, 0.1) 50%, 
                transparent 100%);
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        
        .panel-top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.02) 0%, 
                rgba(255, 255, 255, 0.05) 50%, 
                rgba(255, 255, 255, 0.02) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-secondary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 10;
        }
        
        .dark .panel-top-bar {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.01) 0%, 
                rgba(255, 255, 255, 0.03) 50%, 
                rgba(255, 255, 255, 0.01) 100%);
        }
        
        .panel-product-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .product-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent-blue-light), var(--accent-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        
        .product-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }
        
        .product-status {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .close-button {
            width: 36px;
            height: 36px;
            border: none;
            background: var(--bg-secondary);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            border: 1px solid var(--border-tertiary);
            color: var(--text-secondary);
        }
        
        .close-button:hover {
            background: var(--bg-tertiary);
            transform: scale(1.05);
            box-shadow: 0 4px 12px var(--shadow-medium);
            color: var(--text-primary);
        }
        
        .close-icon {
            width: 18px;
            height: 18px;
        }
        
        .panel-body {
            padding: 28px;
            margin-top: 60px;
            height: calc(100% - 60px);
            overflow-y: auto;
        }
        
        .panel-body::-webkit-scrollbar {
            width: 6px;
        }
        
        .panel-body::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .panel-body::-webkit-scrollbar-thumb {
            background: var(--border-primary);
            border-radius: 3px;
        }
        
        .section {
            margin-bottom: 28px;
        }
        
        .section:last-child {
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
            letter-spacing: -0.01em;
        }
        
        .section-description {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
            font-weight: 400;
        }
        
        .placeholder-box {
            background: var(--bg-secondary);
            border-radius: 20px;
            padding: 28px;
            text-align: center;
            border: 1px dashed var(--border-primary);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
        
        .placeholder-icon {
            width: 56px;
            height: 56px;
            color: var(--text-tertiary);
            margin-bottom: 14px;
        }
        
        .placeholder-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        
        .placeholder-text {
            font-size: 13px;
            color: var(--text-tertiary);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        
        .info-card {
            background: rgba(240, 249, 255, 0.7);
            border-radius: 18px;
            padding: 16px;
            border: 1px solid rgba(219, 234, 254, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        
        .dark .info-card {
            background: rgba(30, 58, 138, 0.3);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        .info-card:last-child {
            background: rgba(240, 253, 244, 0.7);
            border-color: rgba(220, 252, 231, 0.5);
        }
        
        .dark .info-card:last-child {
            background: rgba(20, 83, 45, 0.3);
            border-color: rgba(34, 197, 94, 0.3);
        }
        
        .info-card-title {
            font-size: 13px;
            font-weight: 600;
            color: #3730a3;
            margin-bottom: 6px;
        }
        
        .dark .info-card-title {
            color: #93c5fd;
        }
        
        .info-card:last-child .info-card-title {
            color: #166534;
        }
        
        .dark .info-card:last-child .info-card-title {
            color: #86efac;
        }
        
        .info-card-text {
            font-size: 12px;
            color: #6366f1;
            line-height: 1.4;
        }
        
        .dark .info-card-text {
            color: #a5b4fc;
        }
        
        .info-card:last-child .info-card-text {
            color: #15803d;
        }
        
        .dark .info-card:last-child .info-card-text {
            color: #bbf7d0;
        }
        
        /* Estilos para la sección de reservas */
        .reservations-container {
            height: 100%;
            overflow-y: auto;
        }
        
        .reservations-header {
            margin-bottom: 24px;
        }
        
        .table-layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            margin-bottom: 24px;
        }
        
        .table-simple {
            width: 400px;
            height: 80px;
            background: #6b7280;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }
        
        .seats-simple {
            display: grid;
            grid-template-columns: repeat(11, 1fr);
            gap: 15px;
            width: 100%;
            max-width: 500px;
        }
        
        .seats-top-simple, .seats-bottom-simple {
            display: grid;
            grid-template-columns: repeat(11, 1fr);
            gap: 15px;
            width: 100%;
            max-width: 500px;
        }
        
        .seat-simple {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            border: 2px solid;
        }
        
        .seat-simple.empty {
            background: white;
            border-color: #d1d5db;
            color: #6b7280;
        }
        
        .seat-simple.empty:hover {
            background: #f3f4f6;
            border-color: #3b82f6;
        }
        
        .seat-simple.occupied {
            background: #3b82f6;
            border-color: #2563eb;
            color: white;
        }
        
        .seat-simple.occupied:hover {
            background: #2563eb;
            transform: scale(1.1);
        }
        
        .seat-tooltip-simple {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px;
            min-width: 150px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 1000;
            font-size: 11px;
            line-height: 1.3;
            margin-bottom: 8px;
            color: #000000;
        }
        
        .seat-simple:hover .seat-tooltip-simple {
            opacity: 1;
            visibility: visible;
        }
        
        .table-legend {
            display: flex;
            gap: 24px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .legend-seat {
            width: 20px;
            height: 20px;
            border-radius: 6px;
        }
        
        .legend-seat.empty {
            background: var(--bg-secondary);
            border: 2px solid var(--border-primary);
        }
        
        .legend-seat.occupied {
            background: linear-gradient(135deg, var(--accent-blue-light), var(--accent-blue));
            border: 2px solid var(--accent-blue);
        }
        
        /* Estilos adicionales para productos WooCommerce */
        .item-image {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            background: var(--bg-secondary);
            margin-right: 12px;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-price {
            font-size: 14px;
            font-weight: 600;
            color: var(--accent-blue);
            margin: 4px 0;
        }

        .item-categories {
            font-size: 12px;
            color: var(--text-tertiary);
            margin: 2px 0;
            font-style: italic;
        }

        .item-type {
            display: inline-block;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
            font-weight: 500;
            margin-top: 4px;
        }
        
        /* Scrollbar personalizada para productos */
        .items-wrapper {
            scrollbar-width: thin;
            scrollbar-color: #3b82f6 transparent;
        }
        
        .items-wrapper::-webkit-scrollbar {
            width: 3px;
        }
        
        .items-wrapper::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 2px;
        }
        
        .items-wrapper::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 2px;
            height: 20px;
        }
        
        .items-wrapper::-webkit-scrollbar-thumb:hover {
            background: #2563eb;
        }
        .items-wrapper::-webkit-scrollbar-button {
            display: none;
        }
    </style>
    
    <div class="menu-container">
        <!-- Lista izquierda -->
        <div class="list-container">
            
            
            <div class="items-wrapper">
                @foreach($this->testItems as $item)
                    <div 
                        wire:click="selectItem({{ $item['id'] }})"
                        class="menu-item {{ $selectedItem === $item['id'] ? 'selected' : '' }}"
                    >
                        <div class="item-glass"></div>
                        <div class="item-content">
                            @if(isset($item['image']))
                                <div class="item-image">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" />
                                </div>
                            @endif
                            <div class="item-text">
                                <h4 class="item-name">{{ $item['name'] }}</h4>
                                @if(isset($item['price']) && $item['price'] !== '0')
                                    <p class="item-price">${{ number_format((float)$item['price'], 2) }}</p>
                                @endif
                                @if(isset($item['categories']) && !empty($item['categories']))
                                    <p class="item-categories">{{ $item['categories'] }}</p>
                                @endif
                                @if(isset($item['type']))
                                    <span class="item-type">
                                        @if($item['type'] === 'appointment')
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 4px; vertical-align: middle;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Reserva
                                        @else
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 4px; vertical-align: middle;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                            </svg>
                                            Producto
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="item-indicator">
                                @if($selectedItem === $item['id'])
                                    <svg class="indicator-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="indicator-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Panel derecho -->
        <div class="panel-container">
            @if($selectedItem)
                <div class="panel-content">
                    <!-- Top bar minimalista -->
                    <div class="panel-top-bar">
                        <div class="panel-product-info">
                            <div class="product-avatar">
                                {{ strtoupper(substr($this->selectedItemData['name'], 0, 1)) }}
                            </div>
                            <div class="product-details">
                                <div class="product-name">{{ $this->selectedItemData['name'] }}</div>
                                <div class="product-status">Configuración de horarios</div>
                            </div>
                        </div>
                        <button wire:click="selectItem(null)" class="close-button">
                            <svg class="close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="panel-body">
                        <!-- Tabs Principales -->
                        <div class="tabs-container">
                            <button 
                                wire:click="setActiveTab('reservations')"
                                class="tab-button {{ $activeTab === 'reservations' ? 'active' : '' }}"
                            >
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 6px; vertical-align: middle;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Reservas del Día
                            </button>
                            <button 
                                wire:click="setActiveTab('configuration')"
                                class="tab-button {{ $activeTab === 'configuration' ? 'active' : '' }}"
                            >
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 6px; vertical-align: middle;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Configuración
                            </button>
                        </div>
                        
                        <!-- Tab Reservas del Día -->
                        <div class="tab-content {{ $activeTab === 'reservations' ? 'active' : '' }}">
                            <div class="reservations-container">
                                <div class="reservations-header">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                                        <div>
                                            <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
                                                Reservas Activas - {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') : date('d/m/Y') }}
                                            </h3>
                                            <p style="font-size: 14px; color: var(--text-secondary);">
                                                Vista gráfica de las mesas y su estado actual
                                            </p>
                                        </div>
                                        <div style="display: flex; gap: 12px; align-items: center;">
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                <label style="font-size: 12px; color: var(--text-secondary); font-weight: 500;">Seleccionar fecha:</label>
                                                <div style="display: flex; gap: 8px; align-items: center;">
                                                    <input 
                                                        type="date" 
                                                        wire:model="selectedDate"
                                                        wire:change="changeDate(selectedDate)"
                                                        class="date-input"
                                                        style="padding: 10px 14px; border: 1px solid var(--border-primary); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px; min-width: 150px;"
                                                    >
                                                    <button 
                                                        wire:click="refreshTables()"
                                                        class="tab-button"
                                                        style="padding: 10px 16px; font-size: 14px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; border: 1px solid #3b82f6;"
                                                        onmouseover="this.style.background='#2563eb'; this.style.borderColor='#2563eb';"
                                                        onmouseout="this.style.background='#3b82f6'; this.style.borderColor='#3b82f6';"
                                                    >
                                                        Actualizar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-layout">
                                    <!-- Asientos superiores -->
                                    <div class="seats-top-simple">
                                        @for($i = 1; $i <= 11; $i++)
                                            @if(isset($this->tableSeats[$i]))
                                                <div 
                                                    wire:click="goToCustomer({{ $i }})"
                                                    class="seat-simple {{ $this->tableSeats[$i]['occupied'] ? 'occupied' : 'empty' }}"
                                                >
                                                    @if($this->tableSeats[$i]['occupied'])
                                                        <div class="seat-tooltip-simple">
                                                            <strong>{{ $this->tableSeats[$i]['customer']['name'] }}</strong><br>
                                                            🕐 {{ $this->tableSeats[$i]['customer']['time'] }}<br>
                                                            📞 {{ $this->tableSeats[$i]['customer']['phone'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endfor
                                    </div>
                                    
                                    <!-- Mesa rectangular simple -->
                                    <div class="table-simple">
                                        MESA
                                    </div>
                                    
                                    <!-- Asientos inferiores -->
                                    <div class="seats-bottom-simple">
                                        @for($i = 12; $i <= 22; $i++)
                                            @if(isset($this->tableSeats[$i]))
                                                <div 
                                                    wire:click="goToCustomer({{ $i }})"
                                                    class="seat-simple {{ $this->tableSeats[$i]['occupied'] ? 'occupied' : 'empty' }}"
                                                >
                                                    @if($this->tableSeats[$i]['occupied'])
                                                        <div class="seat-tooltip-simple">
                                                            <strong>{{ $this->tableSeats[$i]['customer']['name'] }}</strong><br>
                                                            🕐 {{ $this->tableSeats[$i]['customer']['time'] }}<br>
                                                            📞 {{ $this->tableSeats[$i]['customer']['phone'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="table-legend">
                                    <div class="legend-item">
                                        <div class="legend-seat empty"></div>
                                        <span>Disponible</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-seat occupied"></div>
                                        <span>Ocupado</span>
                                    </div>
                                </div>
                                
                                <div style="background: #f8f9fa; border-radius: 8px; padding: 16px; border: 1px solid #e9ecef; margin-top: 20px;">
                                    <div style="font-size: 14px; color: #495057; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        Estadísticas del Día
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                        <div style="background: white; border-radius: 6px; padding: 12px; border: 1px solid #dee2e6;">
                                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 4px;">Mesas Ocupadas</div>
                                            <div style="font-size: 18px; color: #212529; font-weight: 600;">{{ collect($this->tableSeats)->where('occupied', true)->count() }} / {{ count($this->tableSeats) }}</div>
                                        </div>
                                        <div style="background: white; border-radius: 6px; padding: 12px; border: 1px solid #dee2e6;">
                                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 4px;">Disponibilidad</div>
                                            <div style="font-size: 18px; color: #212529; font-weight: 600;">{{ round((collect($this->tableSeats)->where('occupied', false)->count() / count($this->tableSeats)) * 100, 1) }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab Configuración -->
                        <div class="tab-content {{ $activeTab === 'configuration' ? 'active' : '' }}">
                            @if($selectedItem)
                                <!-- Tabs de Configuración -->
                                <div class="tabs-container" style="margin-bottom: 24px;">
                                    <button 
                                        wire:click="setConfigurationTab('default')"
                                        class="tab-button {{ $configurationTab === 'default' ? 'active' : '' }}"
                                    >
                                        Horarios por defecto
                                    </button>
                                    <button 
                                        wire:click="setConfigurationTab('custom')"
                                        class="tab-button {{ $configurationTab === 'custom' ? 'active' : '' }}"
                                    >
                                        Modificar por fecha
                                    </button>
                                </div>
                        
                                <!-- Sub-tab 1: Horarios por defecto -->
                                <div class="tab-content {{ $configurationTab === 'default' ? 'active' : '' }}">
                            <div class="schedule-editor">
                                <div class="schedule-section-title">
                                    <span>Horarios por día de la semana</span>
                                    <button wire:click="addDefaultTimeSlot" class="add-button">
                                        + Agregar horario
                                    </button>
                                </div>
                                
                                @foreach($this->itemSchedules['default'] ?? [] as $index => $timeSlot)
                                    <div class="time-slot-item">
                                        <select wire:model.live="itemSchedules.default.{{ $index }}.weekday" class="time-input" style="width: 150px;">
                                            <option value="0">Domingo</option>
                                            <option value="1">Lunes</option>
                                            <option value="2">Martes</option>
                                            <option value="3">Miércoles</option>
                                            <option value="4">Jueves</option>
                                            <option value="5">Viernes</option>
                                            <option value="6">Sábado</option>
                                        </select>
                                        <input 
                                            type="time" 
                                            wire:model.live="itemSchedules.default.{{ $index }}.start_time"
                                            class="time-input"
                                            style="width: 120px;"
                                        />
                                        <input 
                                            type="number" 
                                            wire:model.live="itemSchedules.default.{{ $index }}.priority"
                                            placeholder="Prioridad"
                                            min="0"
                                            max="99"
                                            class="time-input"
                                            style="width: 100px;"
                                        />
                                        <button wire:click="removeDefaultTimeSlot({{ $index }})" class="remove-button">
                                            Eliminar
                                        </button>
                                    </div>
                                @endforeach
                                
                                @if(count($this->itemSchedules['default'] ?? []) === 0)
                                    <div class="empty-state-text">
                                        Sin horarios configurados. Agrega un horario con el botón de arriba.
                                    </div>
                                @endif
                            </div>
                                </div>
                                
                                <!-- Sub-tab 2: Modificar por fecha -->
                                <div class="tab-content {{ $configurationTab === 'custom' ? 'active' : '' }}">
                            <!-- Selector de fecha -->
                            <div class="date-selector">
                                <div style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 12px;">
                                    Selecciona una fecha para modificar sus horarios
                                </div>
                                <input 
                                    type="date" 
                                    wire:model.live="selectedDate"
                                    class="date-input"
                                />
                            </div>
                            
                            @if($selectedDate)
                                <div class="schedule-editor">
                                    <div class="schedule-section-title">
                                        <span>Horarios para {{ $selectedDate }}</span>
                                        <button wire:click="addCustomTimeSlot" class="add-button">
                                            + Agregar horario
                                        </button>
                                    </div>
                                    
                                    @foreach($this->currentCustomSchedule['timeSlots'] ?? [] as $timeIndex => $timeSlot)
                                        <div class="time-slot-item">
                                            <input 
                                                type="time" 
                                                wire:model.live="currentCustomSchedule.timeSlots.{{ $timeIndex }}.start_time"
                                                class="time-input"
                                                style="width: 150px;"
                                                placeholder="Hora"
                                            />
                                            <input 
                                                type="number" 
                                                wire:model.live="currentCustomSchedule.timeSlots.{{ $timeIndex }}.capacity_total"
                                                placeholder="Capacidad total"
                                                min="1"
                                                max="99"
                                                class="time-input"
                                                style="width: 120px;"
                                            />
                                            <button wire:click="removeCustomTimeSlot({{ $timeIndex }})" class="remove-button">
                                                Eliminar
                                            </button>
                                        </div>
                                    @endforeach
                                    
                                    @if(count($this->currentCustomSchedule['timeSlots'] ?? []) === 0)
                                        <div class="empty-state-text">
                                            Sin horarios configurados para esta fecha. Agrega un horario con el botón de arriba.
                                        </div>
                                    @endif
                                </div>
                                
                                <div style="margin-bottom:10px;background: rgba(59, 130, 246, 0.05); border-radius: 16px; padding: 16px; border: 1px solid rgba(59, 130, 246, 0.1);">
                                    <div style="font-size: 13px; color: var(--accent-blue); font-weight: 500; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Información
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.5;">
                                        Los horarios modificados para esta fecha sobrescribirán los horarios por defecto solo en este día específico. 
                                        Si eliminas todos los horarios, se usarán los horarios por defecto.
                                    </div>
                                </div>
                            @else
                                <div class="schedule-editor">
                                    <div class="empty-state-text">
                                        Selecciona una fecha para ver y modificar sus horarios
                                    </div>
                                </div>
                            @endif
                                </div>
                            @else
                                <div class="schedule-editor">
                                    <div class="empty-state-text">
                                        Selecciona un producto para ver la configuración de horarios
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Botón de guardar -->
                            @if($selectedItem)
                                <button wire:click="saveSchedules" class="save-button">
                                    Guardar configuración
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div>
                        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2z"/>
                        </svg>
                        <h3 class="empty-title">Selecciona un producto</h3>
                        <p class="empty-text">Elige un producto de la lista para configurar sus horarios</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Botón flotante para vista de mesa -->
    @if($selectedItem)
        <button wire:click="toggleTableView" class="table-view-button">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
            </svg>
        </button>
    @endif
    
    <!-- Vista de mesa overlay -->
    @if($showTableView)
        <div class="table-view-overlay">
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Vista de Mesa</h2>
                    <button wire:click="toggleTableView" class="table-close-button">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="table-layout">
                    <div class="table-shape">
                        Mesa Principal
                    </div>
                    
                    <div class="seats-container">
                        @foreach($this->tableSeats as $seatNumber => $seat)
                            <div 
                                wire:click="goToCustomer({{ $seatNumber }})"
                                class="seat {{ $seat['occupied'] ? 'occupied' : 'empty' }}"
                            >
                                {{ $seatNumber }}
                                
                                @if($seat['occupied'])
                                    <div class="seat-tooltip">
                                        <div class="tooltip-header">{{ $seat['customer']['name'] }}</div>
                                        <div class="tooltip-info">
                                            <div class="tooltip-item">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>{{ $seat['customer']['time'] }}</span>
                                            </div>
                                            <div class="tooltip-item">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                <span>{{ $seat['customer']['phone'] }}</span>
                                            </div>
                                            <div class="tooltip-item">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <span>{{ $seat['customer']['email'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="table-legend">
                    <div class="legend-item">
                        <div class="legend-seat empty"></div>
                        <span>Disponible</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat occupied"></div>
                        <span>Ocupado</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
