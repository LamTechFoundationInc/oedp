import React from 'react';
import ReplyList from './ReplyList';
import Constants from '../utils/constants';

export default (props) => (
    <div className='rc_comment rc_comment--deleted'>
        <div className="rc_comment-container">
            <div className="rc_avatar">
                <img alt="User avatar" src={Constants.defaultAvatarUrl}/>
            </div>
            <div className="rc_body">
                <div className="rc_comment-details">
                    This comment has been deleted.
                </div>
            </div>
        </div>
        { props.replies &&
        <ReplyList
            {...props}
            replyTo={props.user}
        />
        }
    </div>
);
