{{-- Single modal, filled by main.js from #portfolio-projects for whichever card opened it. --}}
<div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content glass-modal">
            <div class="modal-header">
                <h2 class="modal-title" id="projectModalTitle">Project</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-cover" data-pm="cover"></div>
                <p class="sample-note" data-pm="sample" hidden>
                    <i class="bi bi-info-circle" aria-hidden="true"></i> Sample entry: illustrative only, not real client work.
                </p>
                <ul class="tag-list" data-pm="categories" aria-label="Categories"></ul>
                <p class="modal-lead" data-pm="description"></p>

                <div class="row g-3" data-pm="ps-wrap">
                    <div class="col-md-6" data-pm="problem-wrap">
                        <div class="mini-panel">
                            <h3><i class="bi bi-question-diamond" aria-hidden="true"></i> Problem</h3>
                            <p data-pm="problem"></p>
                        </div>
                    </div>
                    <div class="col-md-6" data-pm="solution-wrap">
                        <div class="mini-panel">
                            <h3><i class="bi bi-lightbulb" aria-hidden="true"></i> Solution</h3>
                            <p data-pm="solution"></p>
                        </div>
                    </div>
                </div>

                <div data-pm="features-wrap">
                    <h3 class="modal-h">Main features</h3>
                    <ul class="tick-list" data-pm="features"></ul>
                </div>

                <div data-pm="challenges-wrap">
                    <h3 class="modal-h">Implementation notes</h3>
                    <p data-pm="challenges"></p>
                </div>

                <div data-pm="stack-wrap">
                    <h3 class="modal-h">Technology stack</h3>
                    <ul class="chip-list" data-pm="stack" aria-label="Technology stack"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <p class="modal-nolinks" data-pm="nolinks" hidden>No public demo or source available for this project.</p>
                <a class="btn btn-glass" data-pm="source" target="_blank" rel="noopener" hidden>
                    <i class="bi bi-github" aria-hidden="true"></i> Source code
                </a>
                <a class="btn btn-glass-primary" data-pm="demo" target="_blank" rel="noopener" hidden>
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Live demo
                </a>
            </div>
        </div>
    </div>
</div>
