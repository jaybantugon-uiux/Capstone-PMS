import React from 'react';
import Sidebar from '../components/Sidebar.jsx';
import DashboardMain from '../components/DashboardMain.jsx';

const ProjectManager = () => {
  return (
    <div className="dashboard-layout">
      <main className="dashboard-main">
        <DashboardMain />
        
      </main>
    </div>
  );
}

export default ProjectManager;