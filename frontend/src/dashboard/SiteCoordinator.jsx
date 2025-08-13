import React from 'react';
import DashboardMain from '../components/DashboardMain.jsx';

const SiteCoordinator = () => {
  return (
    <div className="dashboard-layout">
      <main className="dashboard-main">
        <DashboardMain />
      </main>
    </div>
  );
}

export default SiteCoordinator;