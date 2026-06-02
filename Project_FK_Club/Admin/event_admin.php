<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Approval</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        body{
            background:#f4f7fb;
            font-family:Arial, sans-serif;
        }

        .page-title{
            font-weight:700;
            color:#0b1f4d;
        }

        .approval-card{
            border:none;
            border-radius:18px;
            box-shadow:0 3px 15px rgba(0,0,0,0.05);
        }

        .status-badge{
            padding:8px 16px;
            border-radius:30px;
            font-size:13px;
            font-weight:600;
        }

        .pending{
            background:#fff4db;
            color:#d97706;
        }

        .approved{
            background:#d1f5df;
            color:#15803d;
        }

        .rejected{
            background:#ffe0e0;
            color:#dc2626;
        }

        .action-btn{
            width:38px;
            height:38px;
            border:none;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:white;
            transition:0.3s;
        }

        .approve-btn{
            background:#16a34a;
        }

        .reject-btn{
            background:#dc2626;
        }

        .view-btn{
            background:#2563eb;
        }

        .action-btn:hover{
            transform:translateY(-2px);
        }

        .table th{
            background:#f8fafc;
            color:#444;
        }

    </style>

</head>

<body>

<div class="container py-5">

    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="page-title">
                Event Approval
            </h2>

            <p class="text-muted mb-0">
                Review and approve club event applications.
            </p>

        </div>

        <button class="btn btn-primary rounded-pill px-4">

            <i class="fas fa-filter me-2"></i>
            Filter

        </button>

    </div>

    <!-- EVENT APPROVAL CARD -->

    <div class="card approval-card">

        <div class="card-body p-4">

            <!-- SEARCH -->

            <div class="row mb-4">

                <div class="col-md-4">

                    <input type="text"
                    class="form-control"
                    placeholder="Search event...">

                </div>

                <div class="col-md-3">

                    <select class="form-select">

                        <option>
                            All Status
                        </option>

                        <option>
                            Pending
                        </option>

                        <option>
                            Approved
                        </option>

                        <option>
                            Rejected
                        </option>

                    </select>

                </div>

            </div>

            <!-- TABLE -->

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Event Name</th>
                            <th>Club</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td>1</td>

                            <td>
                                Tech Innovation Talk
                            </td>

                            <td>
                                Developer Club
                            </td>

                            <td>
                                12 June 2026
                            </td>

                            <td>
                                Seminar Hall A
                            </td>

                            <td>

                                <span class="status-badge pending">
                                    Pending
                                </span>

                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <button class="action-btn view-btn">

                                        <i class="fas fa-eye"></i>

                                    </button>

                                    <button class="action-btn approve-btn">

                                        <i class="fas fa-check"></i>

                                    </button>

                                    <button class="action-btn reject-btn">

                                        <i class="fas fa-xmark"></i>

                                    </button>

                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td>2</td>

                            <td>
                                Robotics Workshop
                            </td>

                            <td>
                                Robotics Club
                            </td>

                            <td>
                                18 June 2026
                            </td>

                            <td>
                                Lab 5
                            </td>

                            <td>

                                <span class="status-badge approved">
                                    Approved
                                </span>

                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <button class="action-btn view-btn">

                                        <i class="fas fa-eye"></i>

                                    </button>

                                    <button class="action-btn approve-btn">

                                        <i class="fas fa-check"></i>

                                    </button>

                                    <button class="action-btn reject-btn">

                                        <i class="fas fa-xmark"></i>

                                    </button>

                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td>3</td>

                            <td>
                                Green Campus Campaign
                            </td>

                            <td>
                                Green IT Club
                            </td>

                            <td>
                                25 June 2026
                            </td>

                            <td>
                                Main Courtyard
                            </td>

                            <td>

                                <span class="status-badge rejected">
                                    Rejected
                                </span>

                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <button class="action-btn view-btn">

                                        <i class="fas fa-eye"></i>

                                    </button>

                                    <button class="action-btn approve-btn">

                                        <i class="fas fa-check"></i>

                                    </button>

                                    <button class="action-btn reject-btn">

                                        <i class="fas fa-xmark"></i>

                                    </button>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>




</html>